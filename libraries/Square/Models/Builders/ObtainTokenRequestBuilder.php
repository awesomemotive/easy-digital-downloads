<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ObtainTokenRequest;

/**
 * Builder for model ObtainTokenRequest
 *
 * @see ObtainTokenRequest
 */
class ObtainTokenRequestBuilder
{
    /**
     * @var ObtainTokenRequest
     */
    private $instance;

    private function __construct(ObtainTokenRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Obtain Token Request Builder object.
     *
     * @param string $clientId
     * @param string $grantType
     */
    public static function init(string $clientId, string $grantType): self
    {
        return new self(new ObtainTokenRequest($clientId, $grantType));
    }

    /**
     * Sets client secret field.
     *
     * @param string|null $value
     */
    public function clientSecret(?string $value): self
    {
        $this->instance->setClientSecret($value);
        return $this;
    }

    /**
     * Unsets client secret field.
     */
    public function unsetClientSecret(): self
    {
        $this->instance->unsetClientSecret();
        return $this;
    }

    /**
     * Sets code field.
     *
     * @param string|null $value
     */
    public function code(?string $value): self
    {
        $this->instance->setCode($value);
        return $this;
    }

    /**
     * Unsets code field.
     */
    public function unsetCode(): self
    {
        $this->instance->unsetCode();
        return $this;
    }

    /**
     * Sets redirect uri field.
     *
     * @param string|null $value
     */
    public function redirectUri(?string $value): self
    {
        $this->instance->setRedirectUri($value);
        return $this;
    }

    /**
     * Unsets redirect uri field.
     */
    public function unsetRedirectUri(): self
    {
        $this->instance->unsetRedirectUri();
        return $this;
    }

    /**
     * Sets refresh token field.
     *
     * @param string|null $value
     */
    public function refreshToken(?string $value): self
    {
        $this->instance->setRefreshToken($value);
        return $this;
    }

    /**
     * Unsets refresh token field.
     */
    public function unsetRefreshToken(): self
    {
        $this->instance->unsetRefreshToken();
        return $this;
    }

    /**
     * Sets migration token field.
     *
     * @param string|null $value
     */
    public function migrationToken(?string $value): self
    {
        $this->instance->setMigrationToken($value);
        return $this;
    }

    /**
     * Unsets migration token field.
     */
    public function unsetMigrationToken(): self
    {
        $this->instance->unsetMigrationToken();
        return $this;
    }

    /**
     * Sets scopes field.
     *
     * @param string[]|null $value
     */
    public function scopes(?array $value): self
    {
        $this->instance->setScopes($value);
        return $this;
    }

    /**
     * Unsets scopes field.
     */
    public function unsetScopes(): self
    {
        $this->instance->unsetScopes();
        return $this;
    }

    /**
     * Sets short lived field.
     *
     * @param bool|null $value
     */
    public function shortLived(?bool $value): self
    {
        $this->instance->setShortLived($value);
        return $this;
    }

    /**
     * Unsets short lived field.
     */
    public function unsetShortLived(): self
    {
        $this->instance->unsetShortLived();
        return $this;
    }

    /**
     * Sets code verifier field.
     *
     * @param string|null $value
     */
    public function codeVerifier(?string $value): self
    {
        $this->instance->setCodeVerifier($value);
        return $this;
    }

    /**
     * Unsets code verifier field.
     */
    public function unsetCodeVerifier(): self
    {
        $this->instance->unsetCodeVerifier();
        return $this;
    }

    /**
     * Initializes a new Obtain Token Request object.
     */
    public function build(): ObtainTokenRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
