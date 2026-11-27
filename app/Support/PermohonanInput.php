<?php

namespace App\Support;

use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Validation shared by the public Form Permohonan and the admin "Buat
 * Permohonan" dialog. Text is cleaned before validation (tags and control
 * characters removed, whitespace tidied); it's still escaped on every page
 * and in Telegram messages, so nothing typed here can run as code.
 */
class PermohonanInput
{
    /** Letters (any language), spaces and . ' ’ , - only: no digits. */
    public const NAME_PATTERN = "/^[\\pL\\pM\\s.'’,-]+$/u";

    public static function validate(Request $request, bool $public = false): array
    {
        $request->merge([
            'nama_pemohon' => self::line($request->input('nama_pemohon')),
            'pimpinan_pemohon' => self::line($request->input('pimpinan_pemohon')),
            'kontak_pemohon' => self::line($request->input('kontak_pemohon')),
            'email_pemohon' => self::line($request->input('email_pemohon')),
            'keluhan' => self::text($request->input('keluhan')),
        ]);

        $rules = [
            'nama_pemohon' => ['required', 'string', 'max:100', 'regex:' . self::NAME_PATTERN],
            'pimpinan_pemohon' => ['nullable', 'string', 'max:100', 'regex:' . self::NAME_PATTERN],
            'kontak_pemohon' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9\s-]{6,18}$/'],
            'email_pemohon' => ['nullable', 'email', 'max:255'],
            'id_unit' => ['required', 'exists:unit,id_unit'],
            // Optional, and must belong to the chosen unit.
            'id_sub_unit' => ['nullable', 'integer', Rule::exists('sub_unit', 'id_sub_unit')->where('id_unit', $request->input('id_unit'))],
            'inventaris' => ['required', 'in:y,n'],
            'keluhan' => ['required', 'string', 'min:5', 'max:5000'],
        ];

        if ($public) {
            $rules['recaptcha'] = [new Recaptcha('permohonan')];
        }

        $validated = $request->validate($rules, [
            'nama_pemohon.regex' => 'Nama hanya boleh berisi huruf (tanpa angka).',
            'pimpinan_pemohon.regex' => 'Nama pimpinan hanya boleh berisi huruf (tanpa angka).',
            'kontak_pemohon.regex' => 'No. handphone hanya boleh berisi angka (boleh diawali +).',
            'keluhan.min' => 'Permohonan terlalu singkat.',
            'keluhan.max' => 'Permohonan maksimal 5.000 karakter.',
        ]);

        unset($validated['recaptcha']);

        return $validated;
    }

    /** One line: no tags, control characters or repeated spaces. */
    private static function line(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = strip_tags((string) $value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value === '' ? null : $value;
    }

    /** Multi-line text: no tags or control characters (newlines kept). */
    private static function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = strip_tags(str_replace(["\r\n", "\r"], "\n", (string) $value));
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? '';

        return trim($value) === '' ? null : trim($value);
    }
}
