<?php

namespace Xsolla\LoginSdk\KeyProvider;

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Api\Dto\ProjectPublicKey;
use Xsolla\LoginSdk\Api\LoginApiInterface;
use Xsolla\LoginSdk\Exception\KeyProviderException;
use Xsolla\LoginSdk\Exception\LoginApiException;

class LoginApiKeyProvider implements KeyProviderInterface
{
    private LoginApiInterface $api;

    public function __construct(LoginApiInterface $api)
    {
        $this->api = $api;
    }

    public function getKey(Token $token): Key
    {
        $kid = $token->claims()->get('kid');
        $loginProjectId = $token->claims()->get('xsolla_login_project_id');

        if (empty($loginProjectId)) {
            throw new KeyProviderException('Login project id must be provided');
        }

        try {
            $keys = $this->api->getValidationKeysForLoginProject($loginProjectId);
        } catch (LoginApiException $e) {
            throw new KeyProviderException('Error occurred on get validation keys for login project api request', $e);
        }

        if (empty($keys)) {
            throw new KeyProviderException('There is no public RSA keys available for this project');
        }

        if (!$kid && count($keys) > 1) {
            throw new KeyProviderException('No KID specified and JWKS endpoint returned more than 1 key');
        }

        /** @var ProjectPublicKey[] $compatibleKeys */
        $compatibleKeys = array_filter($keys, function ($k) use ($kid) {
            return !$kid || $k->getKeyIdentifier() === $kid;
        });

        $key = reset($compatibleKeys);

        if (!$key) {
            throw new KeyProviderException('Unable to find a signing key that matches '.$kid);
        }

        return KeyFactory::createFromLoginKey($key);
    }
}
