<?php

namespace App\Http\Controllers;

use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        // Validated by hand so a failure (e.g. reCAPTCHA) still sends the
        // typed email and password back to the form.
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'recaptcha' => [new Recaptcha('login')],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('email', 'password'));
        }

        $credentials = $request->only('email', 'password');

        // Save email to cookie regardless of login result
        cookie()->queue('remember_email', $request->email, 60 * 24 * 30); // 30 days

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'password'));
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