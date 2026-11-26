<?php

namespace App\Support;

use App\Models\KehadiranPasskey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ParagonIE\ConstantTime\Base64UrlSafe;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Verifies passkeys that were registered in Kehadiran, using the same
 * webauthn-lib serializer and validator as Kehadiran's laravel/passkeys, but
 * with Kehadiran's rpId and this app's origin (see config/kehadiran.php).
 */
class KehadiranPasskeys
{
    private const SESSION_KEY = 'kehadiran_passkey_options';

    private ?SerializerInterface $serializer = null;

    /**
     * Start a ceremony: returns options for navigator.credentials.get() and
     * keeps them in the session for verify().
     *
     * @return array<string, mixed>
     */
    public function options(Request $request): array
    {
        $options = PublicKeyCredentialRequestOptions::create(
            challenge: random_bytes(32),
            rpId: config('kehadiran.rp_id'),
            allowCredentials: [],
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            timeout: config('kehadiran.timeout'),
        );

        $json = $this->serializer()->serialize($options, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        $request->session()->put(self::SESSION_KEY, $json);

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Check the browser's assertion against the options from options() and
     * the stored public key. Returns the matching passkey.
     *
     * @throws RuntimeException with a user-facing message when it fails
     */
    public function verify(Request $request, string $assertionJson): KehadiranPasskey
    {
        $optionsJson = $request->session()->pull(self::SESSION_KEY);

        if (! $optionsJson) {
            throw new RuntimeException('Sesi verifikasi kedaluwarsa. Silakan coba lagi.');
        }

        try {
            $options = $this->serializer()->deserialize($optionsJson, PublicKeyCredentialRequestOptions::class, 'json');
            $credential = $this->serializer()->deserialize($assertionJson, PublicKeyCredential::class, 'json');
        } catch (Throwable) {
            throw new RuntimeException('Data passkey tidak valid. Silakan coba lagi.');
        }

        if (! $credential->response instanceof AuthenticatorAssertionResponse) {
            throw new RuntimeException('Data passkey tidak valid. Silakan coba lagi.');
        }

        return DB::transaction(function () use ($credential, $options) {
            $passkey = KehadiranPasskey::where('credential_id', Base64UrlSafe::encodeUnpadded($credential->rawId))
                ->lockForUpdate()
                ->first();

            if (! $passkey) {
                // Usually an old passkey still saved in the phone/password manager
                // after it was removed from Kehadiran, or someone outside TI&TD.
                logger()->warning('Unknown Kehadiran passkey used on login', [
                    'credential_id' => Base64UrlSafe::encodeUnpadded($credential->rawId),
                    'user_handle' => $credential->response->userHandle !== null ? bin2hex($credential->response->userHandle) : null,
                ]);

                throw new RuntimeException('Passkey yang dipilih tidak terdaftar di Kehadiran untuk pegawai Direktorat TI & TD. Jika muncul beberapa passkey, pilih yang terbaru, atau daftarkan ulang di Pengaturan Profil Kehadiran.');
            }

            $record = $this->serializer()->deserialize(json_encode($passkey->credential, JSON_THROW_ON_ERROR), CredentialRecord::class, 'json');

            try {
                $record = $this->validator()->check(
                    credentialRecord: $record,
                    authenticatorAssertionResponse: $credential->response,
                    publicKeyCredentialRequestOptions: $options,
                    host: config('kehadiran.rp_id'),
                    userHandle: $record->userHandle,
                );
            } catch (Throwable $e) {
                report($e);
                throw new RuntimeException('Verifikasi passkey gagal. Silakan coba lagi.');
            }

            // Keep the new signature counter so a cloned authenticator is caught.
            $passkey->forceFill([
                'credential' => json_decode($this->serializer()->serialize($record, 'json'), true, flags: JSON_THROW_ON_ERROR),
                'last_used_at' => now(),
            ])->save();

            return $passkey;
        });
    }

    private function validator(): AuthenticatorAssertionResponseValidator
    {
        $factory = new CeremonyStepManagerFactory;
        $factory->setAllowedOrigins(config('kehadiran.allowed_origins'));
        $factory->setAttestationStatementSupportManager($this->attestationSupport());

        return AuthenticatorAssertionResponseValidator::create(ceremonyStepManager: $factory->requestCeremony());
    }

    private function serializer(): SerializerInterface
    {
        return $this->serializer ??= (new WebauthnSerializerFactory($this->attestationSupport()))->create();
    }

    private function attestationSupport(): AttestationStatementSupportManager
    {
        $manager = AttestationStatementSupportManager::create();
        $manager->add(NoneAttestationStatementSupport::create());

        return $manager;
    }
}
