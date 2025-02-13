<?php

namespace Xsolla\LoginSdk\Api\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * @see https://developers.xsolla.com/login-api/methods/project/get-projects-keys/
 */
final class ProjectPublicKey
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("alg")
     */
    private string $algorithm;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("e")
     */
    private string $pkExponent;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("n")
     */
    private string $pkModulus;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("kid")
     */
    private string $keyIdentifier;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("kty")
     */
    private string $family;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("use")
     */
    private string $use;

    public function __construct(
        string $algorithm,
        string $pkExponent,
        string $pkModulus,
        string $keyIdentifier,
        string $family,
        string $use
    ) {
        $this->algorithm = $algorithm;
        $this->pkExponent = $pkExponent;
        $this->pkModulus = $pkModulus;
        $this->keyIdentifier = $keyIdentifier;
        $this->family = $family;
        $this->use = $use;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function getPkExponent(): string
    {
        return $this->pkExponent;
    }

    public function getPkModulus(): string
    {
        return $this->pkModulus;
    }

    public function getKeyIdentifier(): string
    {
        return $this->keyIdentifier;
    }

    public function getFamily(): string
    {
        return $this->family;
    }

    public function getUse(): string
    {
        return $this->use;
    }
}
