<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceComponentDetailsBatteryDetails;

/**
 * Builder for model DeviceComponentDetailsBatteryDetails
 *
 * @see DeviceComponentDetailsBatteryDetails
 */
class DeviceComponentDetailsBatteryDetailsBuilder
{
    /**
     * @var DeviceComponentDetailsBatteryDetails
     */
    private $instance;

    private function __construct(DeviceComponentDetailsBatteryDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Component Details Battery Details Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceComponentDetailsBatteryDetails());
    }

    /**
     * Sets visible percent field.
     *
     * @param int|null $value
     */
    public function visiblePercent(?int $value): self
    {
        $this->instance->setVisiblePercent($value);
        return $this;
    }

    /**
     * Unsets visible percent field.
     */
    public function unsetVisiblePercent(): self
    {
        $this->instance->unsetVisiblePercent();
        return $this;
    }

    /**
     * Sets external power field.
     *
     * @param string|null $value
     */
    public function externalPower(?string $value): self
    {
        $this->instance->setExternalPower($value);
        return $this;
    }

    /**
     * Initializes a new Device Component Details Battery Details object.
     */
    public function build(): DeviceComponentDetailsBatteryDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
