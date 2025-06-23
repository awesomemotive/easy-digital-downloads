<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceStatus;

/**
 * Builder for model DeviceStatus
 *
 * @see DeviceStatus
 */
class DeviceStatusBuilder
{
    /**
     * @var DeviceStatus
     */
    private $instance;

    private function __construct(DeviceStatus $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Status Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceStatus());
    }

    /**
     * Sets category field.
     *
     * @param string|null $value
     */
    public function category(?string $value): self
    {
        $this->instance->setCategory($value);
        return $this;
    }

    /**
     * Initializes a new Device Status object.
     */
    public function build(): DeviceStatus
    {
        return CoreHelper::clone($this->instance);
    }
}
