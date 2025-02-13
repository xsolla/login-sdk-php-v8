<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Xsolla\LoginSdk\Exception\TokenValidationException;

final class HmacTokenValidator implements TokenValidatorInterface
{
    private Configuration $configuration;

    public function __construct(Key $key)
    {
        $this->configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );

        $this->configuration->setValidationConstraints(
            new Constraint\SignedWith($this->configuration->signer(), $this->configuration->signingKey())
        );
    }

    public function validate(Token $token): void
    {
        $constraints = $this->configuration->validationConstraints();

        try {
            $this->configuration->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            throw TokenValidationException::validationFailed($e->getMessage(), $e);
        }
    }
}
