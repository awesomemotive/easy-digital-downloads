<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceCode;

/**
 * Builder for model DeviceCode
 *
 * @see DeviceCode
 */
class DeviceCodeBuilder
{
    /**
     * @var DeviceCode
     */
    private $instance;

    private function __construct(DeviceCode $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Code Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceCode());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
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
     * Sets device id field.
     *
     * @param string|null $value
     */
    public function deviceId(?string $value): self
    {
        $this->instance->setDeviceId($value);
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets pair by field.
     *
     * @param string|null $value
     */
    public function pairBy(?string $value): self
    {
        $this->instance->setPairBy($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets status changed at field.
     *
     * @param string|null $value
     */
    public function statusChangedAt(?string $value): self
    {
        $this->instance->setStatusChangedAt($value);
        return $this;
    }

    /**
     * Sets paired at field.
     *
     * @param string|null $value
     */
    public function pairedAt(?string $value): self
    {
        $this->instance->setPairedAt($value);
        return $this;
    }

    /**
     * Initializes a new Device Code object.
     */
    public function build(): DeviceCode
    {
        return CoreHelper::clone($this->instance);
    }
}
