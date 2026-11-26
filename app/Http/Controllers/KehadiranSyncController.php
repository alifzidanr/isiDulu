<?php

namespace App\Http\Controllers;

use App\Models\KehadiranPasskey;
use App\Models\KehadiranPegawai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Receives Kehadiran's push of the Direktorat TI&TD Menu TU roster and its
 * passkeys (Kehadiran: `php artisan isidulu:sync-passkeys`). The request is
 * signed: X-Signature = HMAC-SHA256("<X-Timestamp>.<body>", KEHADIRAN_SYNC_SECRET).
 *
 * Each push replaces the roster, so anyone missing from it loses passkey
 * login. Passkeys already known keep their local credential (and with it the
 * signature counter updated by logins here).
 */
class KehadiranSyncController extends Controller
{
    private const MAX_CLOCK_SKEW = 300;

    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('kehadiran.sync_secret');
        $timestamp = (string) $request->header('X-Timestamp');
        $signature = (string) $request->header('X-Signature');

        if (! $secret
            || ! ctype_digit($timestamp)
            || abs(time() - (int) $timestamp) > self::MAX_CLOCK_SKEW
            || ! hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret), $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $data = Validator::make($request->json()->all(), [
            'pegawai' => ['present', 'array'],
            'pegawai.*.id_pegawai' => ['required', 'integer'],
            'pegawai.*.nip' => ['nullable', 'string'],
            'pegawai.*.nama_lengkap' => ['required', 'string'],
            'pegawai.*.email' => ['nullable', 'string'],
            'pegawai.*.passkeys' => ['present', 'array'],
            'pegawai.*.passkeys.*.name' => ['required', 'string'],
            'pegawai.*.passkeys.*.credential_id' => ['required', 'string'],
            'pegawai.*.passkeys.*.credential' => ['required', 'json'],
        ])->validate();

        $now = now();

        DB::transaction(function () use ($data, $now) {
            $known = KehadiranPasskey::pluck('credential', 'credential_id');

            foreach ($data['pegawai'] as $p) {
                KehadiranPegawai::updateOrCreate(['id_pegawai' => $p['id_pegawai']], [
                    'nip' => $p['nip'] ?? null,
                    'nama_lengkap' => $p['nama_lengkap'],
                    'email' => $p['email'] ?? null,
                    'synced_at' => $now,
                ]);

                foreach ($p['passkeys'] as $pk) {
                    KehadiranPasskey::updateOrCreate(['credential_id' => $pk['credential_id']], [
                        'id_pegawai' => $p['id_pegawai'],
                        'name' => $pk['name'],
                        'credential' => $known[$pk['credential_id']] ?? json_decode($pk['credential'], true),
                        'synced_at' => $now,
                    ]);
                }
            }

            // Whatever this push didn't include is gone from Kehadiran or the roster.
            $pegawaiIds = array_column($data['pegawai'], 'id_pegawai');
            $credentialIds = collect($data['pegawai'])->flatMap(fn ($p) => array_column($p['passkeys'], 'credential_id'))->all();
            KehadiranPasskey::whereNotIn('credential_id', $credentialIds)->delete();
            KehadiranPegawai::whereNotIn('id_pegawai', $pegawaiIds)->delete();
        });

        return response()->json([
            'pegawai' => count($data['pegawai']),
            'passkeys' => KehadiranPasskey::count(),
        ]);
    }
}
