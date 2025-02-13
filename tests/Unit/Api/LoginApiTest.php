<?php

namespace Xsolla\LoginSdk\Tests\Unit\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use Xsolla\LoginSdk\Api\Dto\ProjectPublicKey;
use Xsolla\LoginSdk\Api\LoginApi;
use Xsolla\LoginSdk\Exception\LoginApiException;
use Xsolla\LoginSdk\Tests\TokenValues;

class LoginApiTest extends TestCase
{
    const TEST_PROJECT_ID = 'test';

    private MockHandler $responseMock;

    private LoginApi $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseMock = new MockHandler();
        $handlerStack = HandlerStack::create($this->responseMock);

        $client = new Client(['handler' => $handlerStack]);
        $serializer = SerializerBuilder::create()->addDefaultHandlers()->build();

        $this->api = new LoginApi($client, $serializer);
    }

    public function testGetOneValidationKeysForLoginProject()
    {
        $response = sprintf(
            '[{"alg":"%s","e":"%s","n":"%s","kid":"%s","kty":"%s","use":"%s"}]',
            'RS256',
            TokenValues::RSA_PK_EXPONENT,
            TokenValues::RSA_PK_MODULUS,
            'kid',
            'family',
            'use'
        );

        $expected = [
            new ProjectPublicKey(
                'RS256',
                TokenValues::RSA_PK_EXPONENT,
                TokenValues::RSA_PK_MODULUS,
                'kid',
                'family',
                'use'
            ),
        ];

        $this->responseMock->append(new Response(200, [], $response));

        /** @noinspection PhpUnhandledExceptionInspection */
        $actual = $this->api->getValidationKeysForLoginProject(self::TEST_PROJECT_ID);

        $this->assertNotEmpty($actual);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetEmptyValidationKeysForLoginProject()
    {
        $this->responseMock->append(new Response(200, [], '{}'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $keys = $this->api->getValidationKeysForLoginProject(self::TEST_PROJECT_ID);

        $this->assertEmpty($keys);
    }

    public function testExceptionOnGetValidationKeysForLoginProject()
    {
        $this->expectException(LoginApiException::class);

        $this->responseMock->append(new Response(404, []));

        $this->api->getValidationKeysForLoginProject(self::TEST_PROJECT_ID);
    }
}
