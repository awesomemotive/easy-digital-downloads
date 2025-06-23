<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Component;
use EDD\Vendor\Square\Models\Device;
use EDD\Vendor\Square\Models\DeviceAttributes;
use EDD\Vendor\Square\Models\DeviceStatus;

/**
 * Builder for model Device
 *
 * @see Device
 */
class DeviceBuilder
{
    /**
     * @var Device
     */
    private $instance;

    private function __construct(Device $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Builder object.
     *
     * @param DeviceAttributes $attributes
     */
    public static function init(DeviceAttributes $attributes): self
    {
        return new self(new Device($attributes));
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
     * Sets components field.
     *
     * @param Component[]|null $value
     */
    public function components(?array $value): self
    {
        $this->instance->setComponents($value);
        return $this;
    }

    /**
     * Unsets components field.
     */
    public function unsetComponents(): self
    {
        $this->instance->unsetComponents();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param DeviceStatus|null $value
     */
    public function status(?DeviceStatus $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Initializes a new Device object.
     */
    public function build(): Device
    {
        return CoreHelper::clone($this->instance);
    }
}
