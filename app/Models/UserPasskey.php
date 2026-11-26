<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A passkey registered on isiDulu. `credential` is webauthn-lib's
 * serialized CredentialRecord (public key, counter, user handle).
 */
class UserPasskey extends Model
{
    protected $table = 'user_passkeys';

    protected $guarded = [];

    protected $hidden = ['credential'];

    protected $casts = [
        'credential' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
