<?php

namespace Tests\Feature;

use App\Models\KehadiranPasskey;
use App\Models\KehadiranPegawai;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Uid\Uuid;
use Tests\Concerns\PasskeyFixtures;
use Tests\TestCase;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\TrustPath\EmptyTrustPath;

/**
 * Passkeys registered in Kehadiran (rpId kehadiran.test) used to sign in to
 * this app (origin https://isidulu.test), after Kehadiran pushes them, and
 * the isiDulu accounts each push creates for the roster.
 */
class KehadiranPasskeyTest extends TestCase
{
    use PasskeyFixtures;

    private const RP_ID = 'kehadiran.test';

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

    public function test_push_copies_each_member_into_an_isidulu_account(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ANI@al-azhar.or.id'), $this->pegawai(11, 'Budi', null, [])])->assertOk();
        $this->push([$this->pegawai(10, 'Ani', 'ANI@al-azhar.or.id'), $this->pegawai(11, 'Budi', null, [])])->assertOk();

        $this->assertSame(2, User::count());
        $ani = User::where('id_pegawai_kehadiran', 10)->firstOrFail();
        $this->assertSame('ani@al-azhar.or.id', $ani->email);
        $this->assertSame('pegawai-11@kehadiran.local', User::where('id_pegawai_kehadiran', 11)->value('email'));

        // They can sign in with the initial password straight away.
        $this->post('/login', ['email' => 'ani@al-azhar.or.id', 'password' => 'password123'])->assertRedirect();
        $this->assertAuthenticatedAs($ani);
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

    public function test_first_passkey_login_must_set_a_password_first(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);
        $this->postJson('/passkey/login', ['credential' => $this->assertion()])->assertOk();

        $this->get('/dashboard')->assertRedirect(route('profile.edit') . '#ganti-password');
        $this->get('/profil')->assertOk()->assertSee('belum punya kata sandi sendiri');

        // The passkey counts as confirmation: no current password needed.
        $this->put('/profil/password', ['password' => 'rahasia-baru', 'password_confirmation' => 'rahasia-baru'])
            ->assertSessionHasNoErrors();
        $this->assertNotNull(User::where('id_pegawai_kehadiran', 10)->value('password_changed_at'));

        // No longer sent back to the password form (the test DB has no
        // permohonan table, so the dashboard itself can't render here).
        $this->assertFalse($this->get('/dashboard')->isRedirect(route('profile.edit') . '#ganti-password'));
    }

    public function test_password_logins_are_not_redirected(): void
    {
        $this->push([$this->pegawai(10, 'Ani', 'ani@al-azhar.or.id')]);

        $this->post('/login', ['email' => 'ani@al-azhar.or.id', 'password' => 'password123']);

        $this->assertAuthenticated();
        $this->get('/profil')->assertOk()->assertDontSee('belum punya kata sandi sendiri');
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
        $record = CredentialRecord::create(
            publicKeyCredentialId: $this->credentialId,
            type: 'public-key',
            transports: ['internal'],
            attestationType: 'none',
            trustPath: EmptyTrustPath::create(),
            aaguid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            credentialPublicKey: $this->coseKey(),
            userHandle: 'pegawai-'.$idPegawai,
            counter: 0,
        );

        $manager = AttestationStatementSupportManager::create();
        $manager->add(NoneAttestationStatementSupport::create());

        return (new WebauthnSerializerFactory($manager))->create()->serialize($record, 'json');
    }

    private function assertion(string $origin = self::ORIGIN, int $counter = 1): string
    {
        $options = $this->postJson('/passkey/options')->assertOk()->json();

        return $this->signAssertion($options, self::RP_ID, 'pegawai-10', $origin, $counter);
    }
}
