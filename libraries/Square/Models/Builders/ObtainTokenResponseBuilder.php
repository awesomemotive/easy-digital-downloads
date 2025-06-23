<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ObtainTokenResponse;

/**
 * Builder for model ObtainTokenResponse
 *
 * @see ObtainTokenResponse
 */
class ObtainTokenResponseBuilder
{
    /**
     * @var ObtainTokenResponse
     */
    private $instance;

    private function __construct(ObtainTokenResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Obtain Token Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ObtainTokenResponse());
    }

    /**
     * Sets access token field.
     *
     * @param string|null $value
     */
    public function accessToken(?string $value): self
    {
        $this->instance->setAccessToken($value);
        return $this;
    }

    /**
     * Sets token type field.
     *
     * @param string|null $value
     */
    public function tokenType(?string $value): self
    {
        $this->instance->setTokenType($value);
        return $this;
    }

    /**
     * Sets expires at field.
     *
     * @param string|null $value
     */
    public function expiresAt(?string $value): self
    {
        $this->instance->setExpiresAt($value);
        return $this;
    }

    /**
     * Sets merchant id field.
     *
     * @param string|null $value
     */
    public function merchantId(?string $value): self
    {
        $this->instance->setMerchantId($value);
        return $this;
    }

    /**
     * Sets subscription id field.
     *
     * @param string|null $value
     */
    public function subscriptionId(?string $value): self
    {
        $this->instance->setSubscriptionId($value);
        return $this;
    }

    /**
     * Sets plan id field.
     *
     * @param string|null $value
     */
    public function planId(?string $value): self
    {
        $this->instance->setPlanId($value);
        return $this;
    }

    /**
     * Sets id token field.
     *
     * @param string|null $value
     */
    public function idToken(?string $value): self
    {
        $this->instance->setIdToken($value);
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
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets refresh token expires at field.
     *
     * @param string|null $value
     */
    public function refreshTokenExpiresAt(?string $value): self
    {
        $this->instance->setRefreshTokenExpiresAt($value);
        return $this;
    }

    /**
     * Initializes a new Obtain Token Response object.
     */
    public function build(): ObtainTokenResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
