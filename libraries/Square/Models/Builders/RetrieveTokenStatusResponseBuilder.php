<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveTokenStatusResponse;

/**
 * Builder for model RetrieveTokenStatusResponse
 *
 * @see RetrieveTokenStatusResponse
 */
class RetrieveTokenStatusResponseBuilder
{
    /**
     * @var RetrieveTokenStatusResponse
     */
    private $instance;

    private function __construct(RetrieveTokenStatusResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Token Status Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveTokenStatusResponse());
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
     * Sets client id field.
     *
     * @param string|null $value
     */
    public function clientId(?string $value): self
    {
        $this->instance->setClientId($value);
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
     * Initializes a new Retrieve Token Status Response object.
     */
    public function build(): RetrieveTokenStatusResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
