<?php

namespace Xsolla\LoginSdk\KeyProvider\Cache;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\KeyProviderException;

interface TokenCacheItemKeyFactoryInterface
{
    /**
     * @throws KeyProviderException
     */
    public function getCacheItemKey(Token $token): string;
}
