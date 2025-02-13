<?php

namespace Xsolla\LoginSdk\Exception;

use Exception;

class TokenValidationException extends Exception
{
    public static function expired(): self
    {
        return new self('Token is expired');
    }

    public static function hasNoRequiredClaim(string $claim): self
    {
        $message = sprintf('Token has incorrect projectId claim %s', $claim);

        return new self($message);
    }

    public static function validationFailed(string $message, ?Exception $previous = null): self
    {
        $message = sprintf('Token validation failed: %s', $message);

        return new self($message, 422, $previous);
    }
}
