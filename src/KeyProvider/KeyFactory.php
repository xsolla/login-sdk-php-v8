<?php

namespace Xsolla\LoginSdk\KeyProvider;

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use Xsolla\LoginSdk\Api\Dto\ProjectPublicKey;

final class KeyFactory
{
    public static function createFromLoginKey(ProjectPublicKey $projectPublicKey): Key
    {
        $rsa = PublicKeyLoader::load([
            'e' => new BigInteger($projectPublicKey->getPkExponent(), 16),
            'n' => new BigInteger($projectPublicKey->getPkModulus(), 16),
        ]);

        return InMemory::plainText(str_replace("\r\n", "\n", $rsa));
    }
}
