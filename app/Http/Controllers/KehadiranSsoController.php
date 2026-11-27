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
 * Shared sign-in with Kehadiran: the login page sends the browser to
 * Kehadiran once per session (prompt=none). If it's signed in there, it
 * comes back with a token signed by Kehadiran's IsiduluSsoController and is
 * signed in here too; if not, it comes back with `error` and sees the login
 * form. Only Direktorat TI&TD members on the synced roster get in.
 */
class KehadiranSsoController extends Controller
{
    private const STATE_KEY = 'kehadiran_sso_state';

    /** Set once this session has asked Kehadiran (or logged out), so the login page shows the form. */
    public const CHECKED_KEY = 'kehadiran_sso_checked';

    public function redirect(Request $request): RedirectResponse
    {
        $url = config('kehadiran.sso_url');
        abort_unless($url && config('kehadiran.sync_secret'), 404);

        $state = Str::random(40);
        $request->session()->put(self::STATE_KEY, $state);
        $request->session()->put(self::CHECKED_KEY, true);

        return redirect()->away($url.'?'.http_build_query(array_filter([
            'state' => $state,
            'prompt' => $request->query('prompt') === 'none' ? 'none' : null,
        ])));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expected = $request->session()->pull(self::STATE_KEY);

        // Not signed in to Kehadiran, or not TI&TD: just show the login form.
        if ($request->filled('error')) {
            return redirect()->route('login');
        }

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

        Auth::login(KehadiranAccounts::userFor($pegawai));
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
