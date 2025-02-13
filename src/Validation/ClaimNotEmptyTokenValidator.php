<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\TokenValidationException;

class ClaimNotEmptyTokenValidator implements TokenValidatorInterface
{
    private string $claimName;

    public function __construct(string $claimName)
    {
        $this->claimName = $claimName;
    }

    public function validate(Token $token): void
    {
        if (!$token->claims()->has($this->claimName)) {
            throw TokenValidationException::hasNoRequiredClaim($this->claimName);
        }
    }
}
