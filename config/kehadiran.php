<?php

/*
 * Passkey login with credentials registered in Kehadiran (presensiAppYPIA).
 *
 * Those passkeys belong to Kehadiran's relying party (its host). Browsers let
 * this site use them because Kehadiran lists this origin at
 * https://<rp_id>/.well-known/webauthn (WebAuthn Related Origin Requests;
 * Chrome, Edge and Safari, not Firefox yet).
 */
return [

    // Kehadiran's host: the rpId its passkeys were registered for.
    'rp_id' => env('KEHADIRAN_PASSKEY_RP_ID', 'kehadiran.al-azhar.id'),

    // Origins of this app that may complete the ceremony. Behind the
    // Cloudflare tunnel the request itself looks like http://, so APP_URL
    // (https://...) must be right, or list them in KEHADIRAN_ALLOWED_ORIGINS.
    'allowed_origins' => array_values(array_unique(array_filter([
        ...explode(',', (string) env('KEHADIRAN_ALLOWED_ORIGINS', '')),
        rtrim((string) config('app.url'), '/'),
        rescue(fn () => request()->getSchemeAndHttpHost(), report: false),
    ]))),

    // Shared with Kehadiran's ISIDULU_SYNC_SECRET; signs roster pushes.
    'sync_secret' => env('KEHADIRAN_SYNC_SECRET'),

    // Unit (and its kampus) given to accounts created on first passkey login.
    'default_unit_id' => env('KEHADIRAN_DEFAULT_UNIT_ID'),

    'timeout' => 60000,

];
