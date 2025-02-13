<?php

namespace Xsolla\LoginSdk\Validation;

use Lcobucci\JWT\Token;
use Xsolla\LoginSdk\Exception\TokenValidationException;

class ChainTokenValidator implements TokenValidatorInterface
{
    /** @var ChainedTokenValidatorInterface[] */
    private array $validators;

    private string $errorMessage;

    /**
     * @param ChainedTokenValidatorInterface[] $validators
     */
    public function __construct(
        array $validators,
        string $errorMessage
    ) {
        $this->validators = $validators;
        $this->errorMessage = $errorMessage;
    }

    public function validate(Token $token): void
    {
        $atLeastOneSupports = false;

        foreach ($this->validators as $validator) {
            if ($validator->isSupported($token)) {
                $atLeastOneSupports = true;

                $validator->validate($token);
            }
        }

        if (!$atLeastOneSupports) {
            throw TokenValidationException::validationFailed($this->errorMessage);
        }
    }
}
