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
        'id_kampus',
        'id_unit',
        'id_sub_unit',
        'access_level',
    ];

    protected $casts = [
        'password_changed_at' => 'datetime',
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

    public function subUnit()
    {
        return $this->belongsTo(SubUnit::class, 'id_sub_unit', 'id_sub_unit');
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

    /** No kampus and no unit assigned. */
    public function hasNoPlacement(): bool
    {
        return $this->id_kampus === null && $this->id_unit === null;
    }

    /**
     * Without a kampus or unit, an account (other than a superadmin) can see
     * every permohonan but not create, update or report on them.
     */
    public function isReadOnly(): bool
    {
        return ! $this->isSuperAdmin() && $this->hasNoPlacement();
    }
}