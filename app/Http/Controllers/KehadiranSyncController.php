<?php

namespace App\Http\Controllers;

use App\Models\KehadiranPasskey;
use App\Models\KehadiranPegawai;
use App\Support\KehadiranAccounts;
use App\Support\Penandatangan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Receives Kehadiran's push of the Direktorat TI&TD Menu TU roster and its
 * passkeys (Kehadiran: `php artisan isidulu:sync-passkeys`). The request is
 * signed: X-Signature = HMAC-SHA256("<X-Timestamp>.<body>", KEHADIRAN_SYNC_SECRET).
 *
 * Every member gets an isiDulu account (KehadiranAccounts). Each push
 * replaces the roster, so anyone missing from it loses passkey login.
 * Passkeys already known keep their local credential (and with it the
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
            // Signer of printed permohonan (optional; older pushes omit it).
            'penandatangan' => ['sometimes', 'nullable', 'array'],
            'penandatangan.nama_lengkap' => ['required_with:penandatangan', 'string', 'max:150'],
            'penandatangan.tanda_tangan' => ['nullable', 'string', 'max:'.(int) ceil(Penandatangan::MAX_BYTES * 4 / 3) + 4],
        ])->validate();

        if (array_key_exists('penandatangan', $data)) {
            Penandatangan::store($data['penandatangan']);
        }

        $now = now();

        DB::transaction(function () use ($data, $now) {
            $known = KehadiranPasskey::pluck('credential', 'credential_id');

            foreach ($data['pegawai'] as $p) {
                $pegawai = KehadiranPegawai::updateOrCreate(['id_pegawai' => $p['id_pegawai']], [
                    'nip' => $p['nip'] ?? null,
                    'nama_lengkap' => $p['nama_lengkap'],
                    'email' => $p['email'] ?? null,
                    'synced_at' => $now,
                ]);

                // Copy the member into isiDulu so they can sign in and add a passkey here.
                KehadiranAccounts::userFor($pegawai);

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
