<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A passkey registered in Kehadiran. `credential` is webauthn-lib's
 * serialized CredentialRecord (public key, counter, user handle).
 */
class KehadiranPasskey extends Model
{
    protected $table = 'kehadiran_passkeys';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'credential' => 'array',
        'last_used_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(KehadiranPegawai::class, 'id_pegawai', 'id_pegawai');
    }
}
