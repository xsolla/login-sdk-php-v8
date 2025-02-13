<?php

namespace Xsolla\LoginSdk\Tests\Unit\KeyProvider\Cache;

use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Xsolla\LoginSdk\Api\Dto\ProjectPublicKey;
use Xsolla\LoginSdk\Api\LoginApiInterface;
use Xsolla\LoginSdk\Helper\TokenHelper;
use Xsolla\LoginSdk\KeyProvider\Cache\KeyProviderCacheDecorator;
use Xsolla\LoginSdk\KeyProvider\LoginApiKeyProvider;

class ProjectIdTokenClaimCacheKeyFactoryTest extends TestCase
{
    public function testCacheIsWorks()
    {
        $key = InMemory::plainText('test', '');

        $apiMock = $this->createMock(LoginApiInterface::class);

        $apiMock
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->willReturn([new ProjectPublicKey('', '', '', 'test-kid', '', '')]);

        $loginApiKeyProvider = new LoginApiKeyProvider($apiMock);

        $emptyCacheItemMock = $this->createMock(CacheItemInterface::class);
        $emptyCacheItemMock
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $emptyCacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with('test-value');

        $fullCacheItem = $this->createMock(CacheItemInterface::class);
        $fullCacheItem
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $fullCacheItem
            ->expects($this->once())
            ->method('get')
            ->willReturn('test-value');
        $fullCacheItem
            ->expects($this->never())
            ->method('set');

        $cacheItemPoolMock = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPoolMock
            ->expects($this->exactly(2))
            ->method('getItem')
            ->willReturnOnConsecutiveCalls(
                $emptyCacheItemMock,
                $fullCacheItem
            );
        $cacheItemPoolMock
            ->expects($this->once())
            ->method('save')
            ->with($emptyCacheItemMock)
            ->willReturn(true);

        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->willReturn('test-value');
        $serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($key);

        $cachedKeyProvider = new KeyProviderCacheDecorator($loginApiKeyProvider, $cacheItemPoolMock, $serializerMock);

        $token = new Token\Plain(
            new Token\DataSet([], ''),
            new Token\DataSet([
                TokenHelper::CLAIM_KID => 'test-kid',
                TokenHelper::CLAIM_PROJECT_ID => 'test-project-id',
            ], ''),
            Token\Signature::fromEmptyData()
        );

        $cachedKeyProvider->getKey($token);
        $cachedKeyProvider->getKey($token);
    }
}
