<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Api\LoginApiInterface;
use Xsolla\LoginSdk\Exception\LoginApiException;
use Xsolla\LoginSdk\Exception\TokenValidationException;

class HmacApiTokenValidator implements TokenValidatorInterface
{
    private LoginApiInterface $api;

    public function __construct(LoginApiInterface $api)
    {
        $this->api = $api;
    }

    public function validate(Token $token): void
    {
        try {
            $isValid = $this->api->validateHS256Token($token->toString());
        } catch (LoginApiException $e) {
            throw TokenValidationException::validationFailed($e->getMessage(), $e);
        }

        if (!$isValid) {
            throw TokenValidationException::validationFailed('Invalid HS256 token');
        }
    }
}
