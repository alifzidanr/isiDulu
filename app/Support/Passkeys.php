<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserPasskey;
use App\Support\Concerns\UsesWebauthn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ParagonIE\ConstantTime\Base64UrlSafe;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Throwable;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Passkeys registered on isiDulu itself (rpId = this app's host). They're
 * saved on whatever device the member registers them on (laptop, phone),
 * unlike Kehadiran's passkeys, which only work where they were created.
 */
class Passkeys
{
    use UsesWebauthn;

    private const REGISTER_KEY = 'passkey_creation_options';

    private const LOGIN_KEY = 'passkey_request_options';

    /** @return array<string, mixed> options for navigator.credentials.create() */
    public function registerOptions(Request $request, User $user): array
    {
        $options = PublicKeyCredentialCreationOptions::create(
            rp: PublicKeyCredentialRpEntity::create(config('app.name', 'HelpDesk'), $this->rpId()),
            user: PublicKeyCredentialUserEntity::create($user->email, $this->userHandle($user), $user->nama_lengkap),
            challenge: random_bytes(32),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::createPk(-7),   // ES256
                PublicKeyCredentialParameters::createPk(-257), // RS256 (Windows Hello)
            ],
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            excludeCredentials: $user->passkeys()->pluck('credential_id')
                ->map(fn (string $id) => PublicKeyCredentialDescriptor::create(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    Base64UrlSafe::decodeNoPadding($id),
                ))->all(),
            timeout: config('kehadiran.timeout'),
        );

        return $this->remember($request, self::REGISTER_KEY, $options);
    }

    /** Checks the browser's new credential and saves it for the user. */
    public function register(Request $request, User $user, string $attestationJson, string $name): UserPasskey
    {
        $optionsJson = $request->session()->pull(self::REGISTER_KEY);

        if (! $optionsJson) {
            throw new RuntimeException('Sesi pendaftaran kedaluwarsa. Silakan coba lagi.');
        }

        try {
            $options = $this->serializer()->deserialize($optionsJson, PublicKeyCredentialCreationOptions::class, 'json');
            $credential = $this->serializer()->deserialize($attestationJson, PublicKeyCredential::class, 'json');
        } catch (Throwable) {
            throw new RuntimeException('Data passkey tidak valid. Silakan coba lagi.');
        }

        if (! $credential->response instanceof AuthenticatorAttestationResponse
            || $options->user->id !== $this->userHandle($user)) {
            throw new RuntimeException('Data passkey tidak valid. Silakan coba lagi.');
        }

        try {
            $record = AuthenticatorAttestationResponseValidator::create($this->ceremonies()->creationCeremony())
                ->check($credential->response, $options, $this->rpId());
        } catch (Throwable $e) {
            report($e);
            throw new RuntimeException('Pendaftaran passkey gagal. Silakan coba lagi.');
        }

        return $user->passkeys()->create([
            'name' => $name,
            'credential_id' => Base64UrlSafe::encodeUnpadded($record->publicKeyCredentialId),
            'credential' => $this->toArray($record),
        ]);
    }

    /** @return array<string, mixed> options for navigator.credentials.get() (any passkey of this site) */
    public function loginOptions(Request $request): array
    {
        $options = PublicKeyCredentialRequestOptions::create(
            challenge: random_bytes(32),
            rpId: $this->rpId(),
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            timeout: config('kehadiran.timeout'),
        );

        return $this->remember($request, self::LOGIN_KEY, $options);
    }

    /** Checks the browser's assertion; returns the matching passkey. */
    public function verify(Request $request, string $assertionJson): UserPasskey
    {
        $optionsJson = $request->session()->pull(self::LOGIN_KEY);

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
            $passkey = UserPasskey::where('credential_id', Base64UrlSafe::encodeUnpadded($credential->rawId))
                ->lockForUpdate()
                ->first();

            if (! $passkey) {
                throw new RuntimeException('Passkey ini sudah dihapus dari HelpDesk. Masuk dengan kata sandi lalu daftarkan ulang di Pengaturan Profil.');
            }

            $record = $this->serializer()->deserialize(json_encode($passkey->credential, JSON_THROW_ON_ERROR), CredentialRecord::class, 'json');

            try {
                $record = AuthenticatorAssertionResponseValidator::create($this->ceremonies()->requestCeremony())->check(
                    credentialRecord: $record,
                    authenticatorAssertionResponse: $credential->response,
                    publicKeyCredentialRequestOptions: $options,
                    host: $this->rpId(),
                    userHandle: $record->userHandle,
                );
            } catch (Throwable $e) {
                report($e);
                throw new RuntimeException('Verifikasi passkey gagal. Silakan coba lagi.');
            }

            $passkey->forceFill(['credential' => $this->toArray($record), 'last_used_at' => now()])->save();

            return $passkey;
        });
    }

    public function rpId(): string
    {
        return config('passkeys.rp_id') ?: (string) parse_url((string) config('app.url'), PHP_URL_HOST);
    }

    private function userHandle(User $user): string
    {
        return 'isidulu-user-'.$user->id_user;
    }

    /** @return array<string, mixed> */
    private function remember(Request $request, string $key, object $options): array
    {
        $json = $this->serializer()->serialize($options, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
        $request->session()->put($key, $json);

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return array<string, mixed> */
    private function toArray(CredentialRecord $record): array
    {
        return json_decode($this->serializer()->serialize($record, 'json'), true, flags: JSON_THROW_ON_ERROR);
    }
}
