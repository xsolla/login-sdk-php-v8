<?php

namespace Xsolla\LoginSdk\Tests\Unit\KeyProvider;

use Lcobucci\JWT\Token;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xsolla\LoginSdk\Api\LoginApiInterface;
use Xsolla\LoginSdk\Exception\KeyProviderException;
use Xsolla\LoginSdk\Helper\TokenHelper;
use Xsolla\LoginSdk\KeyProvider\KeyFactory;
use Xsolla\LoginSdk\KeyProvider\LoginApiKeyProvider;
use Xsolla\LoginSdk\Tests\ProjectPublicKeyFactory;

class LoginApiKeyProviderTest extends TestCase
{
    const TEST_KID = '1';
    const TEST_PROJECT_ID = 1;

    private LoginApiInterface|MockObject $api;

    private LoginApiKeyProvider $keyProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = $this->createMock(LoginApiInterface::class);

        $this->keyProvider = new LoginApiKeyProvider($this->api);
    }

    public function testUndefinedLoginProjectId()
    {
        $this->expectException(KeyProviderException::class);
        $this->expectExceptionMessage('Login project id must be provided');

        $token = new Token\Plain(
            new Token\DataSet([
                TokenHelper::HEADER_ALG => 'RS256',
            ], ''),
            new Token\DataSet([
                TokenHelper::CLAIM_KID => self::TEST_KID,
            ], ''),
            Token\Signature::fromEmptyData()
        );

        $this->keyProvider->getKey($token);
    }

    public function testEmptyKeys()
    {
        $this->expectException(KeyProviderException::class);
        $this->expectExceptionMessage('There is no public RSA keys available for this project');

        $this->api
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->with(self::TEST_PROJECT_ID)
            ->willReturn([]);

        $token = new Token\Plain(
            new Token\DataSet([
                TokenHelper::HEADER_ALG => 'RS256',
            ], ''),
            new Token\DataSet([
                TokenHelper::CLAIM_KID => self::TEST_KID,
                TokenHelper::CLAIM_PROJECT_ID => self::TEST_PROJECT_ID,
            ], ''),
            Token\Signature::fromEmptyData()
        );

        $this->keyProvider->getKey($token);
    }

    public function testUnspecifiedKid()
    {
        $this->expectException(KeyProviderException::class);
        $this->expectExceptionMessage('No KID specified and JWKS endpoint returned more than 1 key');

        $this->api
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->with(self::TEST_PROJECT_ID)
            ->willReturn([
                ProjectPublicKeyFactory::createRS256Dummy(),
                ProjectPublicKeyFactory::createRS256Dummy(),
            ]);

        $token = new Token\Plain(
            new Token\DataSet([
                TokenHelper::HEADER_ALG => 'RS256',
            ], ''),
            new Token\DataSet([
                TokenHelper::CLAIM_PROJECT_ID => self::TEST_PROJECT_ID,
            ], ''),
            Token\Signature::fromEmptyData()
        );

        $this->keyProvider->getKey($token);
    }

    public function testNoCompatibleKeys()
    {
        $this->expectException(KeyProviderException::class);
        $this->expectExceptionMessage('Unable to find a signing key that matches '.self::TEST_KID);

        $this->api
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->with(self::TEST_PROJECT_ID)
            ->willReturn([
                ProjectPublicKeyFactory::createRS256Dummy(),
                ProjectPublicKeyFactory::createRS256Dummy(),
            ]);

        $token = new Token\Plain(
            new Token\DataSet([
                TokenHelper::HEADER_ALG => 'RS256',
            ], ''),
            new Token\DataSet([
                TokenHelper::CLAIM_KID => self::TEST_KID,
                TokenHelper::CLAIM_PROJECT_ID => self::TEST_PROJECT_ID,
            ], ''),
            Token\Signature::fromEmptyData()
        );

        $this->keyProvider->getKey($token);
    }

    public function testPositiveCase()
    {
        $expected = KeyFactory::createFromLoginKey(ProjectPublicKeyFactory::createRS256ByKid(self::TEST_KID));

        $this->api
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->with(self::TEST_PROJECT_ID)
            ->willReturn([
                ProjectPublicKeyFactory::createRS256Dummy(),
                ProjectPublicKeyFactory::createRS256ByKid(self::TEST_KID),
            ]);

        $token = new Token\Plain(
            new Token\DataSet([
                TokenHelper::HEADER_ALG => 'RS256',
            ], ''),
            new Token\DataSet([
                TokenHelper::CLAIM_KID => self::TEST_KID,
                TokenHelper::CLAIM_PROJECT_ID => self::TEST_PROJECT_ID,
            ], ''),
            Token\Signature::fromEmptyData()
        );

        $actual = $this->keyProvider->getKey($token);

        $this->assertEquals($expected, $actual);
    }
}
