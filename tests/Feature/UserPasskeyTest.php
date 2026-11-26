<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPasskey;
use CBOR\ByteStringObject;
use CBOR\MapItem;
use CBOR\MapObject;
use CBOR\TextStringObject;
use Illuminate\Support\Facades\DB;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Tests\Concerns\PasskeyFixtures;
use Tests\TestCase;

/**
 * Passkeys registered on isiDulu itself (rpId isidulu.test), from
 * Pengaturan Profil, by Direktorat TI&TD members only.
 */
class UserPasskeyTest extends TestCase
{
    use PasskeyFixtures;

    private const RP_ID = 'isidulu.test';

    private function member(): User
    {
        $this->push([['id_pegawai' => 10, 'nip' => '1', 'nama_lengkap' => 'Ani', 'email' => 'ani@al-azhar.or.id', 'passkeys' => []]])->assertOk();

        return User::where('id_pegawai_kehadiran', 10)->firstOrFail();
    }

    public function test_member_registers_a_passkey_then_signs_in_with_it(): void
    {
        $user = $this->member();

        $this->actingAs($user)->postJson('/profil/passkeys', ['credential' => $this->attestation(), 'name' => 'Chrome di macOS'])
            ->assertOk();

        $passkey = UserPasskey::sole();
        $this->assertSame('Chrome di macOS', $passkey->name);
        $this->assertSame($user->id_user, $passkey->id_user);

        auth()->logout();

        $options = $this->postJson('/passkey/options')->assertOk()->json();
        $this->assertSame(self::RP_ID, $options['rpId']);
        $this->assertEmpty($options['allowCredentials'] ?? []); // any passkey saved for this site

        $this->postJson('/passkey/login', ['credential' => $this->signAssertion($options, self::RP_ID, 'isidulu-user-'.$user->id_user)])
            ->assertOk()->assertJsonStructure(['redirect']);
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($passkey->fresh()->last_used_at);
    }

    public function test_registration_from_another_origin_is_refused(): void
    {
        $user = $this->member();

        $this->actingAs($user)->postJson('/profil/passkeys', ['credential' => $this->attestation(origin: 'https://evil.test'), 'name' => 'X'])
            ->assertUnprocessable();
        $this->assertSame(0, UserPasskey::count());
    }

    public function test_accounts_outside_ti_td_cannot_register(): void
    {
        DB::table('user')->insert(['id_kampus' => 1, 'access_level' => 0, 'nama_lengkap' => 'Admin', 'email' => 'admin@x.id', 'password' => 'x', 'status' => 'active']);

        $this->actingAs(User::sole())->postJson('/profil/passkeys/options')->assertForbidden();
    }

    public function test_member_dropped_from_the_roster_can_no_longer_sign_in(): void
    {
        $user = $this->member();
        $this->actingAs($user)->postJson('/profil/passkeys', ['credential' => $this->attestation(), 'name' => 'HP'])->assertOk();
        auth()->logout();

        $this->push([])->assertOk();

        $options = $this->postJson('/passkey/options')->json();
        $this->postJson('/passkey/login', ['credential' => $this->signAssertion($options, self::RP_ID, 'isidulu-user-'.$user->id_user)])
            ->assertForbidden();
        $this->assertGuest();
    }

    public function test_passkey_confirms_password_change_without_current_password(): void
    {
        $user = $this->member();
        $this->actingAs($user)->postJson('/profil/passkeys', ['credential' => $this->attestation(), 'name' => 'HP'])->assertOk();

        $options = $this->postJson('/passkey/options')->json();
        $this->postJson('/passkey/confirm', ['credential' => $this->signAssertion($options, self::RP_ID, 'isidulu-user-'.$user->id_user)])
            ->assertOk();

        $this->put('/profil/password', ['password' => 'rahasia-baru', 'password_confirmation' => 'rahasia-baru'])
            ->assertSessionHasNoErrors();
    }

    public function test_member_can_delete_only_their_own_passkey(): void
    {
        $user = $this->member();
        $this->actingAs($user)->postJson('/profil/passkeys', ['credential' => $this->attestation(), 'name' => 'HP'])->assertOk();
        $passkey = UserPasskey::sole();

        DB::table('user')->insert(['id_kampus' => 1, 'access_level' => 0, 'nama_lengkap' => 'Lain', 'email' => 'lain@x.id', 'password' => 'x', 'status' => 'active']);
        $this->actingAs(User::where('email', 'lain@x.id')->sole())->delete("/profil/passkeys/{$passkey->id}")->assertNotFound();

        $this->actingAs($user)->delete("/profil/passkeys/{$passkey->id}")->assertRedirect();
        $this->assertSame(0, UserPasskey::count());
    }

    /** What navigator.credentials.create() returns ("none" attestation), serialized like resources/js/passkey.js. */
    private function attestation(string $origin = self::ORIGIN): string
    {
        $options = $this->postJson('/profil/passkeys/options')->assertOk()->json();

        $clientData = json_encode([
            'type' => 'webauthn.create',
            'challenge' => $options['challenge'],
            'origin' => $origin,
            'crossOrigin' => false,
        ]);
        $authData = hash('sha256', self::RP_ID, true)
            .chr(0x45) // UP + UV + attested credential data
            .pack('N', 0)
            .str_repeat("\0", 16) // aaguid
            .pack('n', strlen($this->credentialId)).$this->credentialId
            .$this->coseKey();

        $attestationObject = (string) MapObject::create([
            MapItem::create(TextStringObject::create('fmt'), TextStringObject::create('none')),
            MapItem::create(TextStringObject::create('attStmt'), MapObject::create()),
            MapItem::create(TextStringObject::create('authData'), ByteStringObject::create($authData)),
        ]);

        return json_encode([
            'id' => Base64UrlSafe::encodeUnpadded($this->credentialId),
            'rawId' => Base64UrlSafe::encodeUnpadded($this->credentialId),
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => Base64UrlSafe::encodeUnpadded($clientData),
                'attestationObject' => Base64UrlSafe::encodeUnpadded($attestationObject),
                'transports' => ['internal', 'hybrid'],
            ],
        ]);
    }
}
