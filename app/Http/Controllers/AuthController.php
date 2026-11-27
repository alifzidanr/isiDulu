<?php

namespace App\Http\Controllers;

use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Once per session, first ask Kehadiran (silently) whether this browser
     * is already signed in there; if so the pegawai comes back signed in
     * here too (KehadiranSsoController). Otherwise the form is shown.
     */
    public function showLoginForm(Request $request)
    {
        if (config('kehadiran.sso_url') && config('kehadiran.sync_secret')
            && ! $request->session()->has(KehadiranSsoController::CHECKED_KEY)
            && ! $request->session()->has('errors')) {
            return redirect()->route('kehadiran.sso', ['prompt' => 'none']);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'recaptcha' => [new Recaptcha('login')],
        ]);
        unset($credentials['recaptcha']);

        // Save email to cookie regardless of login result
        cookie()->queue('remember_email', $request->email, 60 * 24 * 30); // 30 days

        // Accounts set to inactive in Master User can't sign in.
        if (Auth::attemptWhen($credentials, fn ($user) => $user->status !== 'inactive')) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // Flash the email to session for old() helper
        $request->flash();

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Don't sign straight back in through Kehadiran after logging out.
        $request->session()->put(KehadiranSsoController::CHECKED_KEY, true);

        return redirect('/');
    }
}