<?php

namespace Xsolla\LoginSdk\Tests;

use Xsolla\LoginSdk\Api\Dto\ProjectPublicKey;

class ProjectPublicKeyFactory
{
    public static function createRS256Dummy(): ProjectPublicKey
    {
        return new ProjectPublicKey(
            'RS256',
            TokenValues::RSA_PK_EXPONENT,
            TokenValues::RSA_PK_MODULUS,
            'kid',
            'family',
            'use'
        );
    }

    public static function createRS256ByKid($kid): ProjectPublicKey
    {
        return new ProjectPublicKey(
            'RS256',
            TokenValues::RSA_PK_EXPONENT,
            TokenValues::RSA_PK_MODULUS,
            $kid,
            'family',
            'use'
        );
    }
}
