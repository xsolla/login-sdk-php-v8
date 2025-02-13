<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;

interface ChainedTokenValidatorInterface extends TokenValidatorInterface
{
    public function isSupported(Token $token): bool;
}
