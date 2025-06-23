<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderRoundingAdjustment;

/**
 * Builder for model OrderRoundingAdjustment
 *
 * @see OrderRoundingAdjustment
 */
class OrderRoundingAdjustmentBuilder
{
    /**
     * @var OrderRoundingAdjustment
     */
    private $instance;

    private function __construct(OrderRoundingAdjustment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Rounding Adjustment Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderRoundingAdjustment());
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
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
     * Sets amount money field.
     *
     * @param Money|null $value
     */
    public function amountMoney(?Money $value): self
    {
        $this->instance->setAmountMoney($value);
        return $this;
    }

    /**
     * Initializes a new Order Rounding Adjustment object.
     */
    public function build(): OrderRoundingAdjustment
    {
        return CoreHelper::clone($this->instance);
    }
}
