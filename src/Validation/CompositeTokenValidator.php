<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;

final class CompositeTokenValidator implements TokenValidatorInterface
{
    /** @var TokenValidatorInterface[] */
    private array $validators;

    /**
     * @param TokenValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    public function validate(Token $token): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($token);
        }
    }
}
