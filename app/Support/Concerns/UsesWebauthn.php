<?php

namespace App\Support\Concerns;

use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

/** webauthn-lib plumbing for KehadiranPasskeys. */
trait UsesWebauthn
{
    private ?SerializerInterface $serializer = null;

    private function serializer(): SerializerInterface
    {
        return $this->serializer ??= (new WebauthnSerializerFactory($this->attestationSupport()))->create();
    }

    private function ceremonies(): CeremonyStepManagerFactory
    {
        $factory = new CeremonyStepManagerFactory;
        $factory->setAllowedOrigins(config('kehadiran.allowed_origins'));
        $factory->setAttestationStatementSupportManager($this->attestationSupport());

        return $factory;
    }

    private function attestationSupport(): AttestationStatementSupportManager
    {
        $manager = AttestationStatementSupportManager::create();
        $manager->add(NoneAttestationStatementSupport::create());

        return $manager;
    }
}
