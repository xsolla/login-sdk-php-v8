<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;

final class AlgorithmChainedTokenValidatorDecorator implements ChainedTokenValidatorInterface
{
    private string $algorithm;

    private TokenValidatorInterface $validator;

    public function __construct(
        string $algorithm,
        TokenValidatorInterface $validator
    ) {
        $this->algorithm = $algorithm;
        $this->validator = $validator;
    }

    public function validate(Token $token): void
    {
        $this->validator->validate($token);
    }

    public function isSupported(Token $token): bool
    {
        return $token->headers()->has('alg') && $token->headers()->get('alg') === $this->algorithm;
    }
}
