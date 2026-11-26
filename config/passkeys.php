<?php

/*
 * Passkeys registered on isiDulu itself (App\Support\Passkeys). For
 * passkeys from Kehadiran, see config/kehadiran.php.
 */
return [

    // Defaults to APP_URL's host. Passkeys only work on this host (and its
    // subdomains), so changing it later invalidates every registered passkey.
    'rp_id' => env('PASSKEY_RP_ID'),

];
