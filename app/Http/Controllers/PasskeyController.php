<?php

namespace App\Http\Controllers;

use App\Support\KehadiranAccounts;
use App\Support\KehadiranPasskeys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Fingerprint / Face ID with the YPIA-wide passkeys: registered in Kehadiran
 * or HRIS for rpId al-azhar.id (one shared `passkeys` table), so they work on
 * every *.al-azhar.id app, this one included. Kehadiran pushes the
 * Direktorat TI&TD members' passkeys here; nobody else can use them.
 */
class PasskeyController extends Controller
{
    public function __construct(private KehadiranPasskeys $passkeys) {}

    public function options(Request $request): JsonResponse
    {
        return response()->json($this->passkeys->options($request));
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate(['credential' => ['required', 'string']]);

        try {
            $passkey = $this->passkeys->verify($request, $request->input('credential'));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        Auth::login(KehadiranAccounts::userFor($passkey->pegawai));
        $request->session()->regenerate();

        return response()->json(['redirect' => redirect()->intended(route('dashboard'))->getTargetUrl()]);
    }

    /**
     * Confirms the signed-in user with their passkey, so they can change
     * their password without typing the current one (see ProfileController).
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate(['credential' => ['required', 'string']]);

        try {
            $passkey = $this->passkeys->verify($request, $request->input('credential'));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ((int) $passkey->id_pegawai !== (int) $request->user()->id_pegawai_kehadiran) {
            return response()->json(['message' => 'Passkey ini milik akun lain.'], 422);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return response()->json(['confirmed' => true]);
    }
}
