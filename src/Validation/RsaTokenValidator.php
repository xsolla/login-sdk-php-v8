<?php

namespace Xsolla\LoginSdk\Validation;

use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Xsolla\LoginSdk\Exception\TokenValidationException;
use Xsolla\LoginSdk\KeyProvider\KeyProviderInterface;

final class RsaTokenValidator implements TokenValidatorInterface
{
    private Configuration $configuration;

    private KeyProviderInterface $keyProvider;

    public function __construct(
        KeyProviderInterface $keyProvider
    ) {
        $this->keyProvider = $keyProvider;
    }

    public function validate(Token $token): void
    {
        try {
            $key = $this->keyProvider->getKey($token);

            $this->setConfigurationForKey($key);
            $constraints = $this->configuration->validationConstraints();

            $this->configuration->validator()->assert($token, ...$constraints);
        } catch (Exception $e) {
            throw TokenValidationException::validationFailed($e->getMessage());
        }
    }

    private function setConfigurationForKey($key)
    {
        $this->configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            $key,
            $key
        );

        $this->configuration->setValidationConstraints(
            new Constraint\SignedWith($this->configuration->signer(), $this->configuration->signingKey())
        );
    }
}
