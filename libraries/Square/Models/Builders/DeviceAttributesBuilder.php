<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceAttributes;

/**
 * Builder for model DeviceAttributes
 *
 * @see DeviceAttributes
 */
class DeviceAttributesBuilder
{
    /**
     * @var DeviceAttributes
     */
    private $instance;

    private function __construct(DeviceAttributes $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Attributes Builder object.
     *
     * @param string $manufacturer
     */
    public static function init(string $manufacturer): self
    {
        return new self(new DeviceAttributes($manufacturer));
    }

    /**
     * Sets model field.
     *
     * @param string|null $value
     */
    public function model(?string $value): self
    {
        $this->instance->setModel($value);
        return $this;
    }

    /**
     * Unsets model field.
     */
    public function unsetModel(): self
    {
        $this->instance->unsetModel();
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
     * Sets manufacturers id field.
     *
     * @param string|null $value
     */
    public function manufacturersId(?string $value): self
    {
        $this->instance->setManufacturersId($value);
        return $this;
    }

    /**
     * Unsets manufacturers id field.
     */
    public function unsetManufacturersId(): self
    {
        $this->instance->unsetManufacturersId();
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param string|null $value
     */
    public function version(?string $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets merchant token field.
     *
     * @param string|null $value
     */
    public function merchantToken(?string $value): self
    {
        $this->instance->setMerchantToken($value);
        return $this;
    }

    /**
     * Unsets merchant token field.
     */
    public function unsetMerchantToken(): self
    {
        $this->instance->unsetMerchantToken();
        return $this;
    }

    /**
     * Initializes a new Device Attributes object.
     */
    public function build(): DeviceAttributes
    {
        return CoreHelper::clone($this->instance);
    }
}
