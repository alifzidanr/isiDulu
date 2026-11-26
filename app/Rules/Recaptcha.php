<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Google reCAPTCHA v3: checks the token the page got from grecaptcha.execute()
 * for `$action`. Off until RECAPTCHA_SITE_KEY and RECAPTCHA_SECRET_KEY are set.
 * If Google can't be reached, the login is let through (and logged) rather
 * than locking everyone out.
 */
class Recaptcha implements ValidationRule
{
    /** Run even when the token field is missing from the request. */
    public bool $implicit = true;

    public const MESSAGE = 'Verifikasi keamanan gagal. Muat ulang halaman lalu coba lagi.';

    public function __construct(private string $action = 'login') {}

    public static function enabled(): bool
    {
        return filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret_key'));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::enabled()) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail(self::MESSAGE);

            return;
        }

        try {
            $result = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
            ])->json();
        } catch (ConnectionException $e) {
            logger()->warning('reCAPTCHA unreachable; login allowed', ['error' => $e->getMessage()]);

            return;
        }

        $passed = ($result['success'] ?? false) === true
            && ($result['action'] ?? null) === $this->action
            && (float) ($result['score'] ?? 0) >= (float) config('services.recaptcha.min_score', 0.5);

        if (! $passed) {
            logger()->info('reCAPTCHA rejected a login', array_intersect_key($result ?? [], array_flip(['score', 'action', 'hostname', 'error-codes'])));
            $fail(self::MESSAGE);
        }
    }
}
