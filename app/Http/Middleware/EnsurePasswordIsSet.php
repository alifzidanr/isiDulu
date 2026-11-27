<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Someone who signed in with a passkey (or through Kehadiran) and has never
 * set their own password is sent to Pengaturan Profil to set one before
 * using the rest of the app. Password logins aren't affected.
 */
class EnsurePasswordIsSet
{
    public const VIA_PASSKEY = 'auth.via_passkey';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $request->session()->get(self::VIA_PASSKEY)
            && $user->password_changed_at === null
            && ! $request->routeIs('profile.*', 'logout', 'passkey.confirm')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Buat kata sandi baru terlebih dahulu.'], 403);
            }

            return redirect()->to(route('profile.edit') . '#ganti-password')
                ->with('warning', 'Buat kata sandi baru terlebih dahulu sebelum melanjutkan.');
        }

        return $next($request);
    }

    /** Called right after a passkey / Kehadiran sign-in. */
    public static function markPasskeyLogin(Request $request): void
    {
        // The passkey just proved who they are, so they can set a new password
        // without the current one (ProfileController::recentlyConfirmed()).
        $request->session()->put(self::VIA_PASSKEY, true);
        $request->session()->put('auth.password_confirmed_at', time());
    }
}
