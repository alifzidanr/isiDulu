<?php

namespace Tests\Feature;

use App\Models\KehadiranPasskey;
use App\Models\KehadiranPegawai;
use App\Models\User;
use CBOR\ByteStringObject;
use CBOR\MapItem;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\UnsignedIntegerObject;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Uid\Uuid;
use Tests\TestCase;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\TrustPath\EmptyTrustPath;

/**
 * Passkeys registered in Kehadiran (rpId kehadiran.test) used to sign in to
 * this app (origin https://isidulu.test), after Kehadiran pushes them.
 */
class KehadiranPasskeyTest extends TestCase
{
    private const SECRET = 'test-sync-secret';

    private const RP_ID = 'kehadiran.test';

    private const ORIGIN = 'https://isidulu.test';

    /** @var \OpenSSLAsymmetricKey */
    private $key;

    private string $credentialId;

    protected function setUp(): void
    {
        parent::setUp();

        // The real schema predates migrations; build the bits these tests touch.
        Schema::create('kampus', function (Blueprint $t) {
            $t->increments('id_kampus');
            $t->string('nama_kampus');
        });
        Schema::create('unit', function (Blueprint $t) {
            $t->increments('id_unit');
            $t->integer('id_kampus');
            $t->string('nama_unit');
        });
        Schema::create('user', function (Blueprint $t) {
            $t->increments('id_user');
            $t->integer('id_kampus');
            $t->integer('id_unit')->nullable();
            $t->integer('access_level');
            $t->string('nama_lengkap');
            $t->string('email');
            $t->string('password');
            $t->string('status')->nullable();
            $t->string('telegram_id')->nullable();
            $t->timestamps();
        });
        (require base_path('database/migrations/2026_11_26_000001_create_kehadiran_passkey_tables.php'))->up();

        DB::table('kampus')->insert(['id_kampus' => 1, 'nama_kampus' => 'Kebayoran Baru']);
        DB::table('unit')->insert(['id_unit' => 4, 'id_kampus' => 1, 'nama_unit' => 'IT Support']);

        config([
            'kehadiran.sync_secret' => self::SECRET,
            'kehadiran.rp_id' => self::RP_ID,
            'kehadiran.allowed_origins' => [self::ORIGIN],
            'kehadiran.default_unit_id' => 4,
        ]);

        $this->key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $this->credentialId = random_bytes(16);
    }

    public function test_sync_rejects_an_unsigned_push(): void
    {
        $this->postJson('/kehadiran/sync', ['pegawai' => []], ['X-Timestamp' => (string) time(), 'X-Signature' => 'nope'])
            ->assertUnauthorized();
    }

    public function test_sync_replaces_the_roster(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id'), $this->pegawai(11, 'Budi', null, [])])->assertOk();
        $this->assertSame(2, KehadiranPegawai::count());
        $this->assertSame(1, KehadiranPasskey::count());

        // Ani dropped from Menu TU: her passkey goes with her.
        $this->push([$this->pegawai(11, 'Budi', null, [])])->assertOk();
        $this->assertSame([11], KehadiranPegawai::pluck('id_pegawai')->all());
        $this->assertSame(0, KehadiranPasskey::count());
    }

    public function test_roster_member_signs_in_and_gets_a_user_account(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);

        $this->postJson('/passkey/login', ['credential' => $this->assertion()])
            ->assertOk()
            ->assertJsonStructure(['redirect']);

        $user = User::where('id_pegawai_kehadiran', 10)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(2, (int) $user->access_level);
        $this->assertSame('ani@al-azhar.or.id', $user->email);
        $this->assertSame(4, (int) $user->id_unit);
        $this->assertSame(1, (int) $user->id_kampus);
        $this->assertNotNull(KehadiranPasskey::first()->last_used_at);
    }

    public function test_existing_account_with_same_email_is_linked_not_duplicated(): void
    {
        DB::table('user')->insert([
            'id_kampus' => 1, 'access_level' => 1, 'nama_lengkap' => 'Ani Admin',
            'email' => 'ANI@al-azhar.or.id', 'password' => 'x', 'status' => 'active',
        ]);
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);

        $this->postJson('/passkey/login', ['credential' => $this->assertion()])->assertOk();

        $this->assertSame(1, User::count());
        $this->assertSame(10, (int) User::first()->id_pegawai_kehadiran);
        $this->assertSame(1, (int) User::first()->access_level);
    }

    public function test_passkey_of_someone_not_on_the_roster_is_refused(): void
    {
        $this->push([$this->pegawai(11, 'Budi', null, [])]);

        $this->postJson('/passkey/login', ['credential' => $this->assertion()])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_assertion_from_another_origin_is_refused(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);

        $this->postJson('/passkey/login', ['credential' => $this->assertion(origin: 'https://evil.test')])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_options_only_offer_synced_passkeys(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);

        $options = $this->postJson('/passkey/options')->assertOk()->json();

        $this->assertSame(self::RP_ID, $options['rpId']);
        $this->assertSame([Base64UrlSafe::encodeUnpadded($this->credentialId)], array_column($options['allowCredentials'], 'id'));
    }

    public function test_inactive_account_cannot_sign_in(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);
        $this->postJson('/passkey/login', ['credential' => $this->assertion()])->assertOk();
        auth()->logout();
        User::query()->update(['status' => 'inactive']);

        $this->postJson('/passkey/login', ['credential' => $this->assertion(counter: 2)])->assertForbidden();
        $this->assertGuest();
    }

    /** Signed push, as Kehadiran's isidulu:sync-passkeys sends it. */
    private function push(array $pegawai)
    {
        $body = json_encode(['pegawai' => $pegawai]);
        $ts = (string) time();

        return $this->call('POST', '/kehadiran/sync', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TIMESTAMP' => $ts,
            'HTTP_X_SIGNATURE' => hash_hmac('sha256', $ts.'.'.$body, self::SECRET),
        ], $body);
    }

    private function pegawai(int $id, string $nama, ?string $email, ?array $passkeys = null): array
    {
        return [
            'id_pegawai' => $id,
            'nip' => (string) (90000 + $id),
            'nama_lengkap' => $nama,
            'email' => $email,
            'passkeys' => $passkeys ?? [[
                'name' => 'HP Ani',
                'credential_id' => Base64UrlSafe::encodeUnpadded($this->credentialId),
                'credential' => $this->credentialRecordJson($id),
            ]],
        ];
    }

    /** The credential JSON Kehadiran's laravel/passkeys stores for this key. */
    private function credentialRecordJson(int $idPegawai): string
    {
        $details = openssl_pkey_get_details($this->key)['ec'];
        $cose = MapObject::create([
            MapItem::create(UnsignedIntegerObject::create(1), UnsignedIntegerObject::create(2)),   // kty: EC2
            MapItem::create(UnsignedIntegerObject::create(3), NegativeIntegerObject::create(-7)),  // alg: ES256
            MapItem::create(NegativeIntegerObject::create(-1), UnsignedIntegerObject::create(1)),  // crv: P-256
            MapItem::create(NegativeIntegerObject::create(-2), ByteStringObject::create(str_pad($details['x'], 32, "\0", STR_PAD_LEFT))),
            MapItem::create(NegativeIntegerObject::create(-3), ByteStringObject::create(str_pad($details['y'], 32, "\0", STR_PAD_LEFT))),
        ]);

        $record = CredentialRecord::create(
            publicKeyCredentialId: $this->credentialId,
            type: 'public-key',
            transports: ['internal'],
            attestationType: 'none',
            trustPath: EmptyTrustPath::create(),
            aaguid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            credentialPublicKey: (string) $cose,
            userHandle: 'pegawai-'.$idPegawai,
            counter: 0,
        );

        $manager = AttestationStatementSupportManager::create();
        $manager->add(NoneAttestationStatementSupport::create());

        return (new WebauthnSerializerFactory($manager))->create()->serialize($record, 'json');
    }

    /** What the browser returns from navigator.credentials.get(), serialized like resources/js/passkey.js. */
    private function assertion(string $origin = self::ORIGIN, int $counter = 1): string
    {
        $options = $this->postJson('/passkey/options')->assertOk()->json();

        $clientData = json_encode([
            'type' => 'webauthn.get',
            'challenge' => $options['challenge'],
            'origin' => $origin,
            'crossOrigin' => false,
        ]);
        $authData = hash('sha256', self::RP_ID, true).chr(0x05).pack('N', $counter); // UP + UV
        openssl_sign($authData.hash('sha256', $clientData, true), $signature, $this->key, OPENSSL_ALGO_SHA256);

        return json_encode([
            'id' => Base64UrlSafe::encodeUnpadded($this->credentialId),
            'rawId' => Base64UrlSafe::encodeUnpadded($this->credentialId),
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => Base64UrlSafe::encodeUnpadded($clientData),
                'authenticatorData' => Base64UrlSafe::encodeUnpadded($authData),
                'signature' => Base64UrlSafe::encodeUnpadded($signature),
                'userHandle' => Base64UrlSafe::encodeUnpadded('pegawai-10'),
            ],
        ]);
    }
}
