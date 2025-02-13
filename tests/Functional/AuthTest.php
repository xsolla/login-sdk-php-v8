<?php

namespace Xsolla\LoginSdk\Tests\Functional;

use GuzzleHttp\Client;
use JMS\Serializer\SerializerBuilder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use PHPUnit\Framework\TestCase;
use Xsolla\LoginSdk\Api\LoginApi;
use Xsolla\LoginSdk\Helper\TokenHelper;
use Xsolla\LoginSdk\KeyProvider\LoginApiKeyProvider;
use Xsolla\LoginSdk\Validation\AlgorithmChainedTokenValidatorDecorator;
use Xsolla\LoginSdk\Validation\ChainTokenValidator;
use Xsolla\LoginSdk\Validation\ClaimNotEmptyTokenValidator;
use Xsolla\LoginSdk\Validation\CompositeTokenValidator;
use Xsolla\LoginSdk\Validation\ExpireTokenValidator;
use Xsolla\LoginSdk\Validation\HmacTokenValidator;
use Xsolla\LoginSdk\Validation\RsaTokenValidator;
use Xsolla\LoginSdk\Validation\TokenValidatorInterface;

class AuthTest extends TestCase
{
    public const PA_TOKEN_HMAC = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjIwMzk1Mjc1NzMsImlzcyI6Imh0dHBzOi8vbG9naW4ueHNvbGxhLmNvbSIsImlhdCI6MTU2NDY2MzU3NCwianRpIjoiMTIzNDUiLCJ1c2VybmFtZSI6InRlc3QtbGRhcEB4c29sbGEuY29tIiwicmVkaXJlY3RfdXJsIjoiaHR0cHM6Ly9wdWJsaXNoZXIueHNvbGxhLmNvbS8yMzQwIiwieHNvbGxhX2xvZ2luX2FjY2Vzc19rZXkiOiJUZUdKS0RiNDJVdDMzS2ZqQUIxblVDZmY4cExoMjZKUURCRVdFbExXVEg0Iiwic3ViIjoiZjIzODFhYWMtZTk0MC0xMWU3LThlMzEtNDIwMTBhOGEwMDEzIiwicGFydG5lcl9kYXRhIjp7ImFkbWluIjpmYWxzZX0sInR5cGUiOiJwcm94eSIsInByb3ZpZGVyIjoieHNvbGxhIiwieHNvbGxhX2xvZ2luX3Byb2plY3RfaWQiOiI0MGRiMmVhNC01ZDQyLTExZTYtYTNmZi0wMDUwNTZhMGUwNGEifQ.PEZAf2LJp0bLqVUFo0Rin3qBGEI5wujphfYLNws69Kg';

    public const RSA_TOKEN = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.'
    .'eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.'
    .'POstGetfAytaZS82wHcjoTyoqhMyxXiWdR7Nn7A29DNSl0EiXLdwJ6xC6AfgZWF1bOsS_TuYI3OG85AmiExREkrS6tDfTQ2B3WXlrr-'
    .'wp5AokiRbz3_oB4OxG-W9KcEEbDRcZc0nH3L7LzYptiy1PtAylQGxHTWZXtGz4ht0bAecBgmpdgXMguEIcoqPJ1n3pIWk_'
    .'dUZegpqx0Lka21H6XxUTxiy8OcaarA8zdnPUnV6AmNP3ecFawIFYdvJB_cm-GvpCSbr8G8y_Mllj8f4x9nBH8pQux89_'
    .'6gUY618iYv7tuPWBFfEbLxtF2pZS6YC1aSfLQxeNe8djT9YjpvRZA';

    /** @var TokenValidatorInterface */
    private $validator;

    /** @var Token */
    private $token;

    /** @var Client */
    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client([
            'base_uri' => $_ENV['LOGIN_API'],
            'verify' => false,
        ]);

        $serializer = SerializerBuilder::create()->addDefaultHandlers()->build();

        $api = new LoginApi($this->client, $serializer);

        $keyProvider = new LoginApiKeyProvider($api);
        $rsaTokenValidator = new RsaTokenValidator($keyProvider);

        $key = InMemory::plainText('test-key');
        $hmacTokenValidator = new HmacTokenValidator($key);

        $this->validator = new CompositeTokenValidator([
            new ClaimNotEmptyTokenValidator(TokenHelper::CLAIM_PROJECT_ID),
            new ChainTokenValidator([
                new AlgorithmChainedTokenValidatorDecorator('RS256', $rsaTokenValidator),
                new AlgorithmChainedTokenValidatorDecorator('HS256', $hmacTokenValidator),
            ], 'Incorrect token algorithm'),
            new ExpireTokenValidator(),
        ]);

        $decoder = new JoseEncoder();
        $this->parser = new Parser($decoder);

        $rawToken = $this->generateJWT();
        $this->token = $rawToken;
    }

    /** @test */
    public function itShouldSuccessfullyValidateHmacToken()
    {
        $isValid = $this->validate(self::PA_TOKEN_HMAC);

        $this->assertTrue($isValid);
    }

    /** @test
     * @doesNotPerformAssertions
     */
    public function itShouldSuccessfullyValidateRSAToken()
    {
        $this->validate($this->token['access_token']);
    }

    /** @test */
    public function itShouldRejectsExpiredRsaToken()
    {
        $isValid = $this->validate(
            'eyJhbGciOiJSUzI1NiIsImtpZCI6InNnRnk0NjRrVk5YVFo2YmVYM0tFT2kyam1yWnA4bUQiLCJ0eXAiOiJKV1QifQ.eyJhdWQiOltdLCJlbWFpbCI6ImxvZ2luLXNka0B0ZXN0LmNvbSIsImV4cCI6MTYyMTQyMTU1OSwiZ3JvdXBzIjpbXSwiaWF0IjoxNjIxNDE3OTU4LCJpc19tYXN0ZXIiOnRydWUsImlzcyI6Imh0dHBzOi8vbG9naW4ueHNvbGxhLmNvbSIsImp0aSI6ImZlZDM5ODhiLWJiN2UtNDYzYy04OWU0LWE3MjYxYTNkNWZhMCIsInByb21vX2VtYWlsX2FncmVlbWVudCI6dHJ1ZSwic2NwIjpbIm9mZmxpbmUiXSwic3ViIjoiOTNlMWE5YTMtOGRkZC00OTMxLWE4ZmQtMjA5MGY2M2VkZmI4IiwidHlwZSI6Inhzb2xsYV9sb2dpbiIsInVzZXJuYW1lIjoibG9naW4tc2RrLXRlc3QiLCJ4c29sbGFfbG9naW5fYWNjZXNzX2tleSI6ImZPOGNPc2o4TjdHSWw3MldscVNsYndnSmk0ZGRBOHVQbFd2ZDYxTk1ydnMiLCJ4c29sbGFfbG9naW5fcHJvamVjdF9pZCI6IjQwZGIyZWE0LTVkNDItMTFlNi1hM2ZmLTAwNTA1NmEwZTA0YSJ9.naaIAooeHJWvJwxS9X8QQl3eYce1lWq2YDMeS991s5XaE6K0hQUpbhfAmFrehItf7ivfXSMnhWdVxDPdB6xzdxRGVHIYyLIFZJdIrArLjQ20fKIXcovftS42VdJbyBP8OBGAla0rcmUpywPF7mjMTNsT8l4HKadJEf8901rRELqVPxL8eVvusj-io-f7LKWbinp0nyH3AXOLJ-9KrQ2fuBdVWYBsRiKtto5BgtzOwytCVCdcKDRtIHrAlIObfpyTSlrT2cLJemKmGkQKjsFrP2DkKssfvCgyJGnFo9L51Z98d13FsDn32iaF-zAY0IO45OfOGcS-u4Lv7IP2Tuit_Q'
        );

        $this->assertFalse($isValid);
    }

    private function validate(string $rawToken): bool
    {
        try {
            $token = $this->parser->parse($rawToken);

            $this->validator->validate($token);

            return true;
        } catch (\Throwable $e) {
            var_dump($e->getMessage());

            return false;
        }
    }

    private function generateJWT()
    {
        $code = $this->getLoginCode();
        $response = $this->client->post('/api/oauth2/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $_ENV['LOGIN_CLIENT_ID'],
                'client_secret' => $_ENV['LOGIN_CLIENT_SECRET'],
                'redirect_uri' => 'https://api.xsolla.com/merchant/xsolla_login/session',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function getLoginCode()
    {
        $response = $this->client->post(
            '/api/oauth2/login?response_type=code&client_id=385&scope=offline&state=ABC123xyz&redirect_uri=https://api.xsolla.com/merchant/xsolla_login/session',
            [
                'json' => [
                    'password' => 'login-sdk-test',
                    'username' => 'login-sdk-test',
                ],
            ]
        );

        $content = json_decode($response->getBody()->getContents(), true);
        $parsed = parse_url($content['login_url']);
        parse_str($parsed['query'], $output);

        return $output['code'];
    }
}
