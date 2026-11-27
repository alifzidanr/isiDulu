<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\PasskeyFixtures;
use Tests\TestCase;

/**
 * "Masuk lewat Kehadiran": tokens built the same way as Kehadiran's
 * IsiduluSsoController::token().
 */
class KehadiranSsoTest extends TestCase
{
    use PasskeyFixtures;

    private function startSso(): string
    {
        $location = $this->get('/kehadiran/sso')->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith('https://kehadiran.al-azhar.id/sso/isidulu?state=', $location);
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return $query['state'];
    }

    private function token(array $claims, string $secret = self::SECRET): string
    {
        $b64 = fn (string $bytes) => rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
        $payload = $b64(json_encode($claims + ['aud' => 'isidulu', 'nonce' => bin2hex(random_bytes(8)), 'exp' => time() + 60]));

        return $payload.'.'.$b64(hash_hmac('sha256', $payload, hash_hmac('sha256', 'isidulu-sso', $secret, true), true));
    }

    private function member(): void
    {
        $this->push([['id_pegawai' => 10, 'nip' => '1', 'nama_lengkap' => 'Ani', 'email' => 'ani@al-azhar.or.id', 'passkeys' => []]])->assertOk();
    }

    public function test_titd_member_signs_in_through_kehadiran(): void
    {
        $this->member();
        $state = $this->startSso();

        $this->get('/kehadiran/sso/callback?token='.$this->token(['sub' => 10, 'state' => $state]))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('id_pegawai_kehadiran', 10)->sole());
    }

    public function test_pegawai_not_on_the_roster_is_refused(): void
    {
        $this->member();
        $state = $this->startSso();

        $this->get('/kehadiran/sso/callback?token='.$this->token(['sub' => 99, 'state' => $state]))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_forged_expired_replayed_or_foreign_state_tokens_are_refused(): void
    {
        $this->member();

        $state = $this->startSso();
        $this->get('/kehadiran/sso/callback?token='.$this->token(['sub' => 10, 'state' => $state], 'wrong-secret'))->assertRedirect(route('login'));

        $state = $this->startSso();
        $this->get('/kehadiran/sso/callback?token='.$this->token(['sub' => 10, 'state' => $state, 'exp' => time() - 1]))->assertRedirect(route('login'));

        $this->startSso();
        $this->get('/kehadiran/sso/callback?token='.$this->token(['sub' => 10, 'state' => str_repeat('x', 40)]))->assertRedirect(route('login'));

        $state = $this->startSso();
        $token = $this->token(['sub' => 10, 'state' => $state, 'nonce' => 'same']);
        $this->get('/kehadiran/sso/callback?token='.$token)->assertRedirect(route('dashboard'));
        auth()->logout();
        session()->put('kehadiran_sso_state', $state);
        $this->get('/kehadiran/sso/callback?token='.$token)->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
