<?php

namespace Xsolla\LoginSdk\Api;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use JMS\Serializer\SerializerInterface;
use Xsolla\LoginSdk\Exception\LoginApiException;

class LoginApi implements LoginApiInterface
{
    /** @var Client */
    private $client;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param Client              $client
     * @param SerializerInterface $serializer
     */
    public function __construct($client, $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function getValidationKeysForLoginProject(?string $projectId): array
    {
        try {
            $response = $this->client->get("api/projects/{$projectId}/keys")
                ->getBody()
                ->getContents();

            return $this->serializer->deserialize($response, 'array<Xsolla\LoginSdk\Api\Dto\ProjectPublicKey>', 'json');
        } catch (Exception $e) {
            throw LoginApiException::requestException('Error on project validation keys request', 422, $e);
        }
    }

    public function validateHS256Token(string $token): bool
    {
        try {
            $response = $this->client
                ->post(
                    'api/token/validate',
                    [
                        RequestOptions::JSON => [
                            'token' => $token,
                        ],
                        'http_errors' => false,
                    ]
                );

            return $response->getStatusCode() === 204;
        } catch (Exception $e) {
            throw LoginApiException::requestException('Error on hmac validation request', 422, $e);
        }
    }
}
