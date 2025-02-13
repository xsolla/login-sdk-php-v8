<?php

namespace Xsolla\LoginSdk\Exception;

use Exception;

class LoginApiException extends Exception
{
    public static function requestException($message, $code, $previous): self
    {
        $message = sprintf('Login API request exception: %s', $message);

        return new self($message, $code, $previous);
    }
}
