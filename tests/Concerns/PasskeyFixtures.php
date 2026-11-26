<?php

namespace Tests\Concerns;

use CBOR\ByteStringObject;
use CBOR\MapItem;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\UnsignedIntegerObject;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ParagonIE\ConstantTime\Base64UrlSafe;

/**
 * The legacy tables passkey tests touch, a signed Kehadiran roster push, and
 * a P-256 key standing in for the phone/laptop authenticator.
 */
trait PasskeyFixtures
{
    private const SECRET = 'test-sync-secret';

    private const ORIGIN = 'https://isidulu.test';

    /** @var \OpenSSLAsymmetricKey */
    private $key;

    private string $credentialId;

    /** Run by Laravel's TestCase for classes using this trait. */
    protected function setUpPasskeyFixtures(): void
    {
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
        (require base_path('database/migrations/2026_11_27_000001_create_user_passkeys_table.php'))->up();

        DB::table('kampus')->insert(['id_kampus' => 1, 'nama_kampus' => 'Kebayoran Baru']);
        DB::table('unit')->insert(['id_unit' => 4, 'id_kampus' => 1, 'nama_unit' => 'IT Support']);

        config([
            'app.url' => self::ORIGIN,
            'kehadiran.sync_secret' => self::SECRET,
            'kehadiran.rp_id' => 'kehadiran.test',
            'kehadiran.allowed_origins' => [self::ORIGIN],
            'kehadiran.default_unit_id' => 4,
        ]);

        $this->key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $this->credentialId = random_bytes(16);
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

    /** The authenticator's public key as COSE (EC2, ES256, P-256). */
    private function coseKey(): string
    {
        $ec = openssl_pkey_get_details($this->key)['ec'];

        return (string) MapObject::create([
            MapItem::create(UnsignedIntegerObject::create(1), UnsignedIntegerObject::create(2)),
            MapItem::create(UnsignedIntegerObject::create(3), NegativeIntegerObject::create(-7)),
            MapItem::create(NegativeIntegerObject::create(-1), UnsignedIntegerObject::create(1)),
            MapItem::create(NegativeIntegerObject::create(-2), ByteStringObject::create(str_pad($ec['x'], 32, "\0", STR_PAD_LEFT))),
            MapItem::create(NegativeIntegerObject::create(-3), ByteStringObject::create(str_pad($ec['y'], 32, "\0", STR_PAD_LEFT))),
        ]);
    }

    /** What navigator.credentials.get() returns, serialized like resources/js/passkey.js. */
    private function signAssertion(array $options, string $rpId, string $userHandle, string $origin = self::ORIGIN, int $counter = 1): string
    {
        $clientData = json_encode([
            'type' => 'webauthn.get',
            'challenge' => $options['challenge'],
            'origin' => $origin,
            'crossOrigin' => false,
        ]);
        $authData = hash('sha256', $rpId, true).chr(0x05).pack('N', $counter); // UP + UV
        openssl_sign($authData.hash('sha256', $clientData, true), $signature, $this->key, OPENSSL_ALGO_SHA256);

        return json_encode([
            'id' => Base64UrlSafe::encodeUnpadded($this->credentialId),
            'rawId' => Base64UrlSafe::encodeUnpadded($this->credentialId),
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => Base64UrlSafe::encodeUnpadded($clientData),
                'authenticatorData' => Base64UrlSafe::encodeUnpadded($authData),
                'signature' => Base64UrlSafe::encodeUnpadded($signature),
                'userHandle' => Base64UrlSafe::encodeUnpadded($userHandle),
            ],
        ]);
    }
}
