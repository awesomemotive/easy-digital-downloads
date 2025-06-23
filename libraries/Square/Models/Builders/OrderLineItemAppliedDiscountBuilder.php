<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderLineItemAppliedDiscount;

/**
 * Builder for model OrderLineItemAppliedDiscount
 *
 * @see OrderLineItemAppliedDiscount
 */
class OrderLineItemAppliedDiscountBuilder
{
    /**
     * @var OrderLineItemAppliedDiscount
     */
    private $instance;

    private function __construct(OrderLineItemAppliedDiscount $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Line Item Applied Discount Builder object.
     *
     * @param string $discountUid
     */
    public static function init(string $discountUid): self
    {
        return new self(new OrderLineItemAppliedDiscount($discountUid));
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
     * Sets applied money field.
     *
     * @param Money|null $value
     */
    public function appliedMoney(?Money $value): self
    {
        $this->instance->setAppliedMoney($value);
        return $this;
    }

    /**
     * Initializes a new Order Line Item Applied Discount object.
     */
    public function build(): OrderLineItemAppliedDiscount
    {
        return CoreHelper::clone($this->instance);
    }
}
