<?php

namespace Xsolla\LoginSdk\KeyProvider;

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\KeyProviderException;

interface KeyProviderInterface
{
    /**
     * @throws KeyProviderException
     */
    public function getKey(Token $token): Key;
}
