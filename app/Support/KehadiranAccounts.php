<?php

namespace App\Support;

use App\Models\Kampus;
use App\Models\KehadiranPegawai;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Gives every Direktorat TI&TD member synced from Kehadiran an isiDulu
 * account: an existing link, else the account with the same email, else a
 * new one with access level User in KEHADIRAN_DEFAULT_UNIT_ID.
 */
class KehadiranAccounts
{
    /** Password of accounts created from the roster; members change it in Pengaturan Profil. */
    public const INITIAL_PASSWORD = 'password123';

    public static function userFor(KehadiranPegawai $pegawai): User
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

        $email = $pegawai->email && ! User::whereRaw('lower(email) = ?', [Str::lower($pegawai->email)])->exists()
            ? Str::lower($pegawai->email)
            : 'pegawai-'.$pegawai->id_pegawai.'@kehadiran.local';

        $unit = Unit::find(config('kehadiran.default_unit_id'));

        $user = new User([
            'nama_lengkap' => $pegawai->nama_lengkap,
            'email' => $email,
            'password' => Hash::make(self::INITIAL_PASSWORD),
            'status' => 'active',
            'access_level' => 2,
            'id_unit' => $unit?->id_unit,
            'id_kampus' => $unit?->id_kampus ?? Kampus::orderBy('id_kampus')->value('id_kampus'),
        ]);
        $user->forceFill(['id_pegawai_kehadiran' => $pegawai->id_pegawai])->save();

        return $user;
    }
}
