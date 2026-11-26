<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
use App\Models\KehadiranPegawai;
use App\Models\Unit;
use App\Models\User;
use App\Support\KehadiranPasskeys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Fingerprint / Face ID login with passkeys registered in Kehadiran. Only
 * Direktorat TI&TD Menu TU members (synced from Kehadiran) can use it; the
 * first login creates their isiDulu account with access level User.
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

        $user = $this->userFor($passkey->pegawai);

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Akun HelpDesk Anda tidak aktif. Hubungi admin.'], 403);
        }

        Auth::login($user);
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

    /**
     * The isiDulu account linked to this pegawai: an existing link, else an
     * account with the same email, else a new one with access level User.
     */
    private function userFor(KehadiranPegawai $pegawai): User
    {
        $user = User::where('id_pegawai_kehadiran', $pegawai->id_pegawai)->first();

        if (! $user && $pegawai->email) {
            $user = User::whereRaw('lower(email) = ?', [Str::lower($pegawai->email)])
                ->whereNull('id_pegawai_kehadiran')
                ->first();
        }

        if ($user) {
            if ($user->id_pegawai_kehadiran === null) {
                $user->forceFill(['id_pegawai_kehadiran' => $pegawai->id_pegawai])->save();
            }

            return $user;
        }

        $email = $pegawai->email && ! User::where('email', $pegawai->email)->exists()
            ? $pegawai->email
            : 'pegawai-'.$pegawai->id_pegawai.'@kehadiran.local';

        $unit = Unit::find(config('kehadiran.default_unit_id'));

        $user = new User([
            'nama_lengkap' => $pegawai->nama_lengkap,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'status' => 'active',
            'access_level' => 2,
            'id_unit' => $unit?->id_unit,
            'id_kampus' => $unit?->id_kampus ?? Kampus::orderBy('id_kampus')->value('id_kampus'),
        ]);
        $user->forceFill(['id_pegawai_kehadiran' => $pegawai->id_pegawai])->save();

        return $user;
    }
}
