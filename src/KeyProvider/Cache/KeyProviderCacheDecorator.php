<?php

namespace Xsolla\LoginSdk\KeyProvider\Cache;

use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Psr\Cache\CacheItemPoolInterface;
use Xsolla\LoginSdk\KeyProvider\KeyProviderInterface;

final class KeyProviderCacheDecorator implements KeyProviderInterface
{
    const CACHE_ITEM_TTL_DEFAULT = 300;

    private KeyProviderInterface $decorated;

    private CacheItemPoolInterface $cacheItemPool;

    private SerializerInterface $serializer;

    private int $cacheItemTtl;

    private TokenCacheItemKeyFactoryInterface $cacheItemKeyFactory;

    public function __construct(
        KeyProviderInterface $decorated,
        CacheItemPoolInterface $cacheItemPool,
        SerializerInterface $serializer,
        int $cacheItemTtl = self::CACHE_ITEM_TTL_DEFAULT,
        TokenCacheItemKeyFactoryInterface $cacheItemKeyFactory = null
    ) {
        $this->decorated = $decorated;
        $this->cacheItemPool = $cacheItemPool;
        $this->serializer = $serializer;
        $this->cacheItemTtl = $cacheItemTtl;
        $this->cacheItemKeyFactory = $cacheItemKeyFactory ?? new ProjectIdTokenClaimCacheKeyFactory();
    }

    public function getKey(Token $token): Key
    {
        $cacheItemKey = $this->cacheItemKeyFactory->getCacheItemKey($token);

        $cacheItem = $this->cacheItemPool->getItem($cacheItemKey);

        if (!$cacheItem->isHit()) {
            $cacheItemValue = $cacheItem->get();

            return $this->deserializeKey($cacheItemValue);
        }

        $key = $this->decorated->getKey($token);

        $cacheItemValue = $this->serializeKey($key);

        $cacheItem->set($cacheItemValue);
        $cacheItem->expiresAfter($this->cacheItemTtl);

        $this->cacheItemPool->save($cacheItem);

        return $key;
    }

    private function deserializeKey(string $cacheItemValue): Key
    {
        return $this->serializer->deserialize($cacheItemValue, Key::class, 'json');
    }

    private function serializeKey(Key $key): string
    {
        return $this->serializer->serialize($key, 'json');
    }
}
