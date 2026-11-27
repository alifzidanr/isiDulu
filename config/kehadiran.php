<?php

/*
 * Passkey login with the YPIA-wide passkeys. Kehadiran and HRIS register
 * them for the parent domain al-azhar.id in one shared `passkeys` table, so
 * any *.al-azhar.id site (this app runs at layanan-ti.al-azhar.id) can use
 * them directly. Kehadiran pushes the Direktorat TI&TD members' public keys
 * here (KehadiranSyncController).
 */
return [

    // The rpId the shared passkeys are registered for (PASSKEYS_RELYING_PARTY_ID
    // in Kehadiran and HRIS). This app must be served from it or a subdomain.
    'rp_id' => env('KEHADIRAN_PASSKEY_RP_ID', 'al-azhar.id'),

    // Origins of this app that may complete the ceremony. Behind the
    // Cloudflare tunnel the request itself looks like http://, so APP_URL
    // (https://...) must be right, or list them in KEHADIRAN_ALLOWED_ORIGINS.
    'allowed_origins' => array_values(array_unique(array_filter([
        ...explode(',', (string) env('KEHADIRAN_ALLOWED_ORIGINS', '')),
        rtrim((string) config('app.url'), '/'),
        rescue(fn () => request()->getSchemeAndHttpHost(), report: false),
    ]))),

    // Shared with Kehadiran's ISIDULU_SYNC_SECRET; signs roster pushes and SSO tokens.
    'sync_secret' => env('KEHADIRAN_SYNC_SECRET'),

    // Signer printed bottom right on a permohonan. Kehadiran's push supplies
    // the name and signature (Penandatangan); these are the fallbacks.
    'penandatangan' => [
        'nama' => env('PENANDATANGAN_NAMA', 'Doni Sutrisno'),
        'jabatan' => env('PENANDATANGAN_JABATAN', 'Penanggung Jawab HelpDesk'),
    ],

    // Unit (and its kampus) given to accounts created for roster members.
    'default_unit_id' => env('KEHADIRAN_DEFAULT_UNIT_ID'),

    'timeout' => 60000,

    // "Masuk lewat Kehadiran" (KehadiranSsoController): Kehadiran's sign-in
    // hand-off page. Tokens are signed with a key derived from sync_secret.
    'sso_url' => env('KEHADIRAN_SSO_URL', 'https://kehadiran.al-azhar.id/sso/isidulu'),

];
