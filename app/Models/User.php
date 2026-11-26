<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';
    
    protected $fillable = [
        'nama_lengkap',
        'email',
        'telegram_id',
        'password',
        'status',
        'id_kampus',
        'id_unit',
        'access_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** The Kehadiran pegawai this account signs in as with a passkey. */
    public function kehadiranPegawai()
    {
        return $this->belongsTo(KehadiranPegawai::class, 'id_pegawai_kehadiran', 'id_pegawai');
    }

    /** Passkeys registered on isiDulu itself (see App\Support\Passkeys). */
    public function passkeys()
    {
        return $this->hasMany(UserPasskey::class, 'id_user', 'id_user')->latest();
    }

    /** Passkey login is only for current Direktorat TI&TD members (synced from Kehadiran). */
    public function canUsePasskeys(): bool
    {
        return $this->id_pegawai_kehadiran !== null
            && KehadiranPegawai::whereKey($this->id_pegawai_kehadiran)->exists();
    }

    public function kampus()
    {
        return $this->belongsTo(Kampus::class, 'id_kampus');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'id_user');
    }

    public function isSuperAdmin()
    {
        return $this->access_level === 0;
    }

    public function isAdmin()
    {
        return $this->access_level === 1;
    }

    public function isUser()
    {
        return $this->access_level === 2;
    }
}