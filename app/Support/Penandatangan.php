<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Who signs printed permohonan (bottom right): the person in charge of
 * isiDulu, with the signature they uploaded in Kehadiran. Kehadiran sends
 * both in its roster push (KehadiranSyncController); they're kept in private
 * storage here and replaced on every push.
 */
class Penandatangan
{
    private const IMAGE = 'penandatangan/tanda-tangan.png';

    private const INFO = 'penandatangan/info.json';

    /** Largest signature accepted from Kehadiran. */
    public const MAX_BYTES = 1024 * 1024;

    /**
     * @param  array{nama_lengkap: string, tanda_tangan?: string|null}|null  $data
     */
    public static function store(?array $data): void
    {
        if ($data === null) {
            Storage::delete([self::IMAGE, self::INFO]);

            return;
        }

        Storage::put(self::INFO, json_encode(['nama' => $data['nama_lengkap'], 'synced_at' => now()->toIso8601String()], JSON_THROW_ON_ERROR));

        // Only a valid PNG replaces the saved signature. A push without one
        // (e.g. Kehadiran can't read it) keeps whatever is already here,
        // including one set by hand with `php artisan penandatangan:tanda-tangan`.
        $png = isset($data['tanda_tangan']) ? base64_decode($data['tanda_tangan'], true) : false;

        if ($png !== false) {
            self::storeImage($png);
        }
    }

    /** Save a signature PNG. Returns false (and keeps the old one) for anything else. */
    public static function storeImage(string $png): bool
    {
        if (strlen($png) > self::MAX_BYTES || ! str_starts_with($png, "\x89PNG\r\n\x1a\n")) {
            return false;
        }

        // put() returns false (not an exception) when the file can't be written.
        return Storage::put(self::IMAGE, $png) === true;
    }

    /**
     * @return array{nama: string|null, jabatan: string, gambar: string|null}
     */
    public static function current(): array
    {
        $info = Storage::exists(self::INFO) ? json_decode(Storage::get(self::INFO), true) : null;

        return [
            'nama' => $info['nama'] ?? config('kehadiran.penandatangan.nama'),
            'jabatan' => config('kehadiran.penandatangan.jabatan'),
            'gambar' => Storage::exists(self::IMAGE) ? 'data:image/png;base64,'.base64_encode(Storage::get(self::IMAGE)) : null,
        ];
    }
}
