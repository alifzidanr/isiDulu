<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPasskey;
use App\Support\KehadiranAccounts;
use App\Support\KehadiranPasskeys;
use App\Support\Passkeys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Fingerprint / Face ID login, only for Direktorat TI&TD Menu TU members
 * (synced from Kehadiran, each with an isiDulu account):
 *  - passkeys registered here, in Pengaturan Profil (any device), and
 *  - passkeys registered in Kehadiran (only on the device that made them).
 */
class PasskeyController extends Controller
{
    public function __construct(private Passkeys $passkeys, private KehadiranPasskeys $kehadiran) {}

    public function options(Request $request): JsonResponse
    {
        return response()->json($this->passkeys->loginOptions($request));
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate(['credential' => ['required', 'string']]);

        try {
            $user = $this->passkeys->verify($request, $request->input('credential'))->user;
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (! $user->canUsePasskeys()) {
            return response()->json(['message' => 'Login fingerprint/Face ID khusus pegawai Direktorat TI & TD.'], 403);
        }

        return $this->signIn($request, $user);
    }

    public function kehadiranOptions(Request $request): JsonResponse
    {
        return response()->json($this->kehadiran->options($request));
    }

    public function kehadiranLogin(Request $request): JsonResponse
    {
        $request->validate(['credential' => ['required', 'string']]);

        try {
            $passkey = $this->kehadiran->verify($request, $request->input('credential'));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $this->signIn($request, KehadiranAccounts::userFor($passkey->pegawai));
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

        if ((int) $passkey->id_user !== (int) $request->user()->id_user) {
            return response()->json(['message' => 'Passkey ini milik akun lain.'], 422);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return response()->json(['confirmed' => true]);
    }

    public function registerOptions(Request $request): JsonResponse
    {
        abort_unless($request->user()->canUsePasskeys(), 403, 'Passkey khusus pegawai Direktorat TI & TD.');

        return response()->json($this->passkeys->registerOptions($request, $request->user()));
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->canUsePasskeys(), 403, 'Passkey khusus pegawai Direktorat TI & TD.');

        $data = $request->validate([
            'credential' => ['required', 'string'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        try {
            $this->passkeys->register($request, $request->user(), $data['credential'], $data['name']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        session()->flash('success', 'Passkey berhasil didaftarkan.');

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, UserPasskey $passkey): RedirectResponse
    {
        abort_unless((int) $passkey->id_user === (int) $request->user()->id_user, 404);

        $passkey->delete();

        return back()->with('success', 'Passkey dihapus.');
    }

    private function signIn(Request $request, User $user): JsonResponse
    {
        if ($user->status !== 'active') {
            return response()->json(['message' => 'Akun HelpDesk Anda tidak aktif. Hubungi admin.'], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['redirect' => redirect()->intended(route('dashboard'))->getTargetUrl()]);
    }
}
