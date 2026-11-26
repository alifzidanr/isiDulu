<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A Direktorat TI&TD pegawai from Kehadiran's Menu TU roster, as last pushed
 * by Kehadiran. Only people in this table can sign in with a passkey.
 */
class KehadiranPegawai extends Model
{
    protected $table = 'kehadiran_pegawai';

    protected $primaryKey = 'id_pegawai';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['synced_at' => 'datetime'];

    public function passkeys(): HasMany
    {
        return $this->hasMany(KehadiranPasskey::class, 'id_pegawai', 'id_pegawai');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id_pegawai_kehadiran', 'id_pegawai');
    }
}
