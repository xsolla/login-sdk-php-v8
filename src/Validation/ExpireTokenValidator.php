<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\TokenValidationException;
use Xsolla\LoginSdk\Helper\TokenHelper;

class ExpireTokenValidator implements TokenValidatorInterface
{
    public function validate(Token $token): void
    {
        $expiresAt = $token->claims()->get(TokenHelper::CLAIM_EXP);

        if ($expiresAt === null) {
            return;
        }

        if ($expiresAt instanceof \DateTimeImmutable) {
            $expiresAt = $expiresAt->getTimestamp();
        }

        $now = time();

        if ($now >= $expiresAt) {
            throw TokenValidationException::expired();
        }
    }
}
