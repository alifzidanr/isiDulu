<?php

namespace App\Http\Controllers;

use App\Models\KehadiranPegawai;
use App\Support\KehadiranAccounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * "Masuk lewat Kehadiran": the browser goes to Kehadiran, signs in there
 * (passkey, password or an existing session) and comes back with a token
 * signed by Kehadiran's IsiduluSsoController. Only Direktorat TI&TD members
 * on the synced roster (kehadiran_pegawai) get in.
 */
class KehadiranSsoController extends Controller
{
    private const STATE_KEY = 'kehadiran_sso_state';

    public function redirect(Request $request): RedirectResponse
    {
        $url = config('kehadiran.sso_url');
        abort_unless($url && config('kehadiran.sync_secret'), 404);

        $state = Str::random(40);
        $request->session()->put(self::STATE_KEY, $state);

        return redirect()->away($url.'?'.http_build_query(['state' => $state]));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expected = $request->session()->pull(self::STATE_KEY);
        $claims = $this->claims((string) $request->query('token', ''));

        if (! $claims
            || ! is_string($expected)
            || ! hash_equals($expected, (string) ($claims['state'] ?? ''))
            || ($claims['aud'] ?? null) !== 'isidulu'
            || (int) ($claims['exp'] ?? 0) < time()
            || ! Cache::add('kehadiran-sso-nonce:'.($claims['nonce'] ?? ''), true, 300)) {
            return $this->fail('Masuk lewat Kehadiran gagal atau kedaluwarsa. Silakan coba lagi.');
        }

        // Double-check against our own copy of the roster as well.
        $pegawai = KehadiranPegawai::find((int) ($claims['sub'] ?? 0));
        if (! $pegawai) {
            return $this->fail('HelpDesk hanya untuk pegawai Direktorat TI & TD.');
        }

        $user = KehadiranAccounts::userFor($pegawai);
        if ($user->status !== 'active') {
            return $this->fail('Akun HelpDesk Anda tidak aktif. Hubungi admin.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Berhasil masuk lewat Kehadiran.');
    }

    /** @return array<string, mixed>|null the token's claims if Kehadiran's signature checks out */
    private function claims(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }
        [$payload, $signature] = $parts;

        $key = hash_hmac('sha256', 'isidulu-sso', (string) config('kehadiran.sync_secret'), true);
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', $payload, $key, true)), '+/', '-_'), '=');

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $claims = json_decode((string) base64_decode(strtr($payload, '-_', '+/')), true);

        return is_array($claims) ? $claims : null;
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['failed' => $message]);
    }
}
