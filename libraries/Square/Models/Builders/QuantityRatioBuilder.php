<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\QuantityRatio;

/**
 * Builder for model QuantityRatio
 *
 * @see QuantityRatio
 */
class QuantityRatioBuilder
{
    /**
     * @var QuantityRatio
     */
    private $instance;

    private function __construct(QuantityRatio $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Quantity Ratio Builder object.
     */
    public static function init(): self
    {
        return new self(new QuantityRatio());
    }

    /**
     * Sets quantity field.
     *
     * @param int|null $value
     */
    public function quantity(?int $value): self
    {
        $this->instance->setQuantity($value);
        return $this;
    }

    /**
     * Unsets quantity field.
     */
    public function unsetQuantity(): self
    {
        $this->instance->unsetQuantity();
        return $this;
    }

    /**
     * Sets quantity denominator field.
     *
     * @param int|null $value
     */
    public function quantityDenominator(?int $value): self
    {
        $this->instance->setQuantityDenominator($value);
        return $this;
    }

    /**
     * Unsets quantity denominator field.
     */
    public function unsetQuantityDenominator(): self
    {
        $this->instance->unsetQuantityDenominator();
        return $this;
    }

    /**
     * Initializes a new Quantity Ratio object.
     */
    public function build(): QuantityRatio
    {
        return CoreHelper::clone($this->instance);
    }
}
