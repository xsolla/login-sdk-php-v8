<?php

namespace Xsolla\LoginSdk\KeyProvider\Cache;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\KeyProviderException;
use Xsolla\LoginSdk\Helper\TokenHelper;

final class ProjectIdTokenClaimCacheKeyFactory implements TokenCacheItemKeyFactoryInterface
{
    const CACHE_KEY_PATTERN_DEFAULT = 'login_api.%s.public_key';

    private string $cacheItemKeyPattern;

    public function __construct(string $cacheItemKeyPattern = self::CACHE_KEY_PATTERN_DEFAULT)
    {
        $this->cacheItemKeyPattern = $cacheItemKeyPattern;
    }

    public function getCacheItemKey(Token $token): string
    {
        $id = $token->claims()->get(TokenHelper::CLAIM_PROJECT_ID);

        if (empty($id)) {
            throw new KeyProviderException('Login project id must be provided');
        }

        return sprintf($this->cacheItemKeyPattern, $id);
    }
}
