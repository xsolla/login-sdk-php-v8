<?php

namespace Xsolla\LoginSdk\Tests\Unit\Validation;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xsolla\LoginSdk\Api\LoginApiInterface;
use Xsolla\LoginSdk\Exception\TokenValidationException;
use Xsolla\LoginSdk\Helper\TokenHelper;
use Xsolla\LoginSdk\KeyProvider\LoginApiKeyProvider;
use Xsolla\LoginSdk\Tests\ProjectPublicKeyFactory;
use Xsolla\LoginSdk\Tests\TokenValues;
use Xsolla\LoginSdk\Validation\AlgorithmChainedTokenValidatorDecorator;
use Xsolla\LoginSdk\Validation\ChainTokenValidator;
use Xsolla\LoginSdk\Validation\ClaimNotEmptyTokenValidator;
use Xsolla\LoginSdk\Validation\CompositeTokenValidator;
use Xsolla\LoginSdk\Validation\ExpireTokenValidator;
use Xsolla\LoginSdk\Validation\HmacApiTokenValidator;
use Xsolla\LoginSdk\Validation\RsaTokenValidator;
use Xsolla\LoginSdk\Validation\TokenValidatorInterface;

class TokenValidationTest extends TestCase
{
    private Parser $tokenParser;

    private LoginApiInterface|MockObject $api;

    private TokenValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenParser = new Parser(new JoseEncoder());

        $this->api = $this->createMock(LoginApiInterface::class);

        $keyProvider = new LoginApiKeyProvider($this->api);
        $rsaTokenValidator = new RsaTokenValidator($keyProvider);

        $hmacTokenValidator = new HmacApiTokenValidator($this->api);

        $this->validator = new CompositeTokenValidator([
            new ClaimNotEmptyTokenValidator(TokenHelper::CLAIM_PROJECT_ID),
            new ChainTokenValidator([
                new AlgorithmChainedTokenValidatorDecorator('RS256', $rsaTokenValidator),
                new AlgorithmChainedTokenValidatorDecorator('HS256', $hmacTokenValidator),
            ], 'Incorrect token algorithm'),
            new ExpireTokenValidator(),
        ]);
    }

    public function testRsaTokenIsValid()
    {
        $projectLoginKey = ProjectPublicKeyFactory::createRS256ByKid(TokenValues::KID);

        $this->api
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->willReturn([$projectLoginKey]);

        $this->api
            ->expects($this->never())
            ->method('validateHS256Token');

        $token = $this->tokenParser->parse(TokenValues::TOKEN_RSA_VALID);

        $this->validator->validate($token);
    }

    public function testRsaTokenExpired()
    {
        $this->expectException(TokenValidationException::class);
        $this->expectExceptionMessage('Token is expired');

        $projectLoginKey = ProjectPublicKeyFactory::createRS256ByKid(TokenValues::KID);

        $this->api
            ->expects($this->once())
            ->method('getValidationKeysForLoginProject')
            ->willReturn([$projectLoginKey]);

        $this->api
            ->expects($this->never())
            ->method('validateHS256Token');

        $token = $this->tokenParser->parse(TokenValues::TOKEN_RSA_EXPIRED);

        $this->validator->validate($token);
    }

    public function testHmacRemoteTokenIsValid()
    {
        $this->api
            ->expects($this->never())
            ->method('getValidationKeysForLoginProject');

        $this->api
            ->expects($this->once())
            ->method('validateHS256Token')
            ->willReturn(true);

        $token = $this->tokenParser->parse(TokenValues::TOKEN_HMAC_REMOTE);

        $this->validator->validate($token);
    }

    public function testHmacLocalTokenNotSupported()
    {
        $this->expectException(TokenValidationException::class);
        $this->expectExceptionMessage('Token has incorrect projectId claim xsolla_login_project_id');

        $this->api
            ->expects($this->never())
            ->method('getValidationKeysForLoginProject');

        $this->api
            ->expects($this->never())
            ->method('validateHS256Token');

        $token = $this->tokenParser->parse(TokenValues::TOKEN_HMAC_LOCAL);

        $this->validator->validate($token);
    }
}
