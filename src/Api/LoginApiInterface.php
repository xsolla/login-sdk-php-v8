<?php

namespace Xsolla\LoginSdk\Api;

use Xsolla\LoginSdk\Api\Dto\ProjectPublicKey;
use Xsolla\LoginSdk\Exception\LoginApiException;

interface LoginApiInterface
{
    /**
     * @return ProjectPublicKey[]
     *
     * @throws LoginApiException
     */
    public function getValidationKeysForLoginProject(?string $projectId): array;

    /**
     * @throws LoginApiException
     */
    public function validateHS256Token(string $token): bool;
}
