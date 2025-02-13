<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\TokenValidationException;

interface TokenValidatorInterface
{
    /**
     * Validate the given token.
     *
     * @throws TokenValidationException
     */
    public function validate(Token $token): void;
}
