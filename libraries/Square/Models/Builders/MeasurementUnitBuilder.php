<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\MeasurementUnit;
use EDD\Vendor\Square\Models\MeasurementUnitCustom;

/**
 * Builder for model MeasurementUnit
 *
 * @see MeasurementUnit
 */
class MeasurementUnitBuilder
{
    /**
     * @var MeasurementUnit
     */
    private $instance;

    private function __construct(MeasurementUnit $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Measurement Unit Builder object.
     */
    public static function init(): self
    {
        return new self(new MeasurementUnit());
    }

    /**
     * Sets custom unit field.
     *
     * @param MeasurementUnitCustom|null $value
     */
    public function customUnit(?MeasurementUnitCustom $value): self
    {
        $this->instance->setCustomUnit($value);
        return $this;
    }

    /**
     * Sets area unit field.
     *
     * @param string|null $value
     */
    public function areaUnit(?string $value): self
    {
        $this->instance->setAreaUnit($value);
        return $this;
    }

    /**
     * Sets length unit field.
     *
     * @param string|null $value
     */
    public function lengthUnit(?string $value): self
    {
        $this->instance->setLengthUnit($value);
        return $this;
    }

    /**
     * Sets volume unit field.
     *
     * @param string|null $value
     */
    public function volumeUnit(?string $value): self
    {
        $this->instance->setVolumeUnit($value);
        return $this;
    }

    /**
     * Sets weight unit field.
     *
     * @param string|null $value
     */
    public function weightUnit(?string $value): self
    {
        $this->instance->setWeightUnit($value);
        return $this;
    }

    /**
     * Sets generic unit field.
     *
     * @param string|null $value
     */
    public function genericUnit(?string $value): self
    {
        $this->instance->setGenericUnit($value);
        return $this;
    }

    /**
     * Sets time unit field.
     *
     * @param string|null $value
     */
    public function timeUnit(?string $value): self
    {
        $this->instance->setTimeUnit($value);
        return $this;
    }

    /**
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Initializes a new Measurement Unit object.
     */
    public function build(): MeasurementUnit
    {
        return CoreHelper::clone($this->instance);
    }
}
