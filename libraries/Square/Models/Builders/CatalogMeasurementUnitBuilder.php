<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogMeasurementUnit;
use EDD\Vendor\Square\Models\MeasurementUnit;

/**
 * Builder for model CatalogMeasurementUnit
 *
 * @see CatalogMeasurementUnit
 */
class CatalogMeasurementUnitBuilder
{
    /**
     * @var CatalogMeasurementUnit
     */
    private $instance;

    private function __construct(CatalogMeasurementUnit $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Measurement Unit Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogMeasurementUnit());
    }

    /**
     * Sets measurement unit field.
     *
     * @param MeasurementUnit|null $value
     */
    public function measurementUnit(?MeasurementUnit $value): self
    {
        $this->instance->setMeasurementUnit($value);
        return $this;
    }

    /**
     * Sets precision field.
     *
     * @param int|null $value
     */
    public function precision(?int $value): self
    {
        $this->instance->setPrecision($value);
        return $this;
    }

    /**
     * Unsets precision field.
     */
    public function unsetPrecision(): self
    {
        $this->instance->unsetPrecision();
        return $this;
    }

    /**
     * Initializes a new Catalog Measurement Unit object.
     */
    public function build(): CatalogMeasurementUnit
    {
        return CoreHelper::clone($this->instance);
    }
}
