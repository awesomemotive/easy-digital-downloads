<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceComponentDetailsMeasurement;

/**
 * Builder for model DeviceComponentDetailsMeasurement
 *
 * @see DeviceComponentDetailsMeasurement
 */
class DeviceComponentDetailsMeasurementBuilder
{
    /**
     * @var DeviceComponentDetailsMeasurement
     */
    private $instance;

    private function __construct(DeviceComponentDetailsMeasurement $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Component Details Measurement Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceComponentDetailsMeasurement());
    }

    /**
     * Sets value field.
     *
     * @param int|null $value
     */
    public function value(?int $value): self
    {
        $this->instance->setValue($value);
        return $this;
    }

    /**
     * Unsets value field.
     */
    public function unsetValue(): self
    {
        $this->instance->unsetValue();
        return $this;
    }

    /**
     * Initializes a new Device Component Details Measurement object.
     */
    public function build(): DeviceComponentDetailsMeasurement
    {
        return CoreHelper::clone($this->instance);
    }
}
