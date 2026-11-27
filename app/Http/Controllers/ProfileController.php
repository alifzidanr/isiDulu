<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pengaturan Profil: each account edits its own contact details and
 * password (modelled on Kehadiran's profile page).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['kampus', 'unit', 'subUnit', 'kehadiranPegawai.passkeys']);

        return view('profile.edit', [
            'user' => $user,
            'passkeyConfirmed' => $this->recentlyConfirmed($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user', 'email')->ignore($user->id_user, 'id_user')],
            'telegram_id' => ['nullable', 'string', 'max:100', Rule::unique('user', 'telegram_id')->ignore($user->id_user, 'id_user')],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * current_password may be left blank only if the session was just
     * confirmed with the user's passkey (PasskeyController::confirm()).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['nullable', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ]);

        $user = $request->user();
        $current = $validated['current_password'] ?? '';

        if ($current !== '') {
            if (! Hash::check($current, $user->password)) {
                throw ValidationException::withMessages(['current_password' => 'Kata sandi saat ini salah.']);
            }
        } elseif (! $this->recentlyConfirmed($request)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini wajib diisi, atau verifikasi dengan fingerprint/Face ID.',
            ]);
        }

        $user->forceFill(['password' => Hash::make($validated['password'])])->save();
        $request->session()->forget('auth.password_confirmed_at');

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }

    private function recentlyConfirmed(Request $request): bool
    {
        $at = (int) $request->session()->get('auth.password_confirmed_at', 0);

        return $at > 0 && (time() - $at) < config('auth.password_timeout', 10800);
    }
}
