<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Coordinates;

/**
 * Builder for model Coordinates
 *
 * @see Coordinates
 */
class CoordinatesBuilder
{
    /**
     * @var Coordinates
     */
    private $instance;

    private function __construct(Coordinates $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Coordinates Builder object.
     */
    public static function init(): self
    {
        return new self(new Coordinates());
    }

    /**
     * Sets latitude field.
     *
     * @param float|null $value
     */
    public function latitude(?float $value): self
    {
        $this->instance->setLatitude($value);
        return $this;
    }

    /**
     * Unsets latitude field.
     */
    public function unsetLatitude(): self
    {
        $this->instance->unsetLatitude();
        return $this;
    }

    /**
     * Sets longitude field.
     *
     * @param float|null $value
     */
    public function longitude(?float $value): self
    {
        $this->instance->setLongitude($value);
        return $this;
    }

    /**
     * Unsets longitude field.
     */
    public function unsetLongitude(): self
    {
        $this->instance->unsetLongitude();
        return $this;
    }

    /**
     * Initializes a new Coordinates object.
     */
    public function build(): Coordinates
    {
        return CoreHelper::clone($this->instance);
    }
}
