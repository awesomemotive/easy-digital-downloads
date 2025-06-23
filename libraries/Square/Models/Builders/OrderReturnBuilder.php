<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderMoneyAmounts;
use EDD\Vendor\Square\Models\OrderReturn;
use EDD\Vendor\Square\Models\OrderReturnDiscount;
use EDD\Vendor\Square\Models\OrderReturnLineItem;
use EDD\Vendor\Square\Models\OrderReturnServiceCharge;
use EDD\Vendor\Square\Models\OrderReturnTax;
use EDD\Vendor\Square\Models\OrderReturnTip;
use EDD\Vendor\Square\Models\OrderRoundingAdjustment;

/**
 * Builder for model OrderReturn
 *
 * @see OrderReturn
 */
class OrderReturnBuilder
{
    /**
     * @var OrderReturn
     */
    private $instance;

    private function __construct(OrderReturn $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Return Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderReturn());
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
     * Sets source order id field.
     *
     * @param string|null $value
     */
    public function sourceOrderId(?string $value): self
    {
        $this->instance->setSourceOrderId($value);
        return $this;
    }

    /**
     * Unsets source order id field.
     */
    public function unsetSourceOrderId(): self
    {
        $this->instance->unsetSourceOrderId();
        return $this;
    }

    /**
     * Sets return line items field.
     *
     * @param OrderReturnLineItem[]|null $value
     */
    public function returnLineItems(?array $value): self
    {
        $this->instance->setReturnLineItems($value);
        return $this;
    }

    /**
     * Unsets return line items field.
     */
    public function unsetReturnLineItems(): self
    {
        $this->instance->unsetReturnLineItems();
        return $this;
    }

    /**
     * Sets return service charges field.
     *
     * @param OrderReturnServiceCharge[]|null $value
     */
    public function returnServiceCharges(?array $value): self
    {
        $this->instance->setReturnServiceCharges($value);
        return $this;
    }

    /**
     * Unsets return service charges field.
     */
    public function unsetReturnServiceCharges(): self
    {
        $this->instance->unsetReturnServiceCharges();
        return $this;
    }

    /**
     * Sets return taxes field.
     *
     * @param OrderReturnTax[]|null $value
     */
    public function returnTaxes(?array $value): self
    {
        $this->instance->setReturnTaxes($value);
        return $this;
    }

    /**
     * Sets return discounts field.
     *
     * @param OrderReturnDiscount[]|null $value
     */
    public function returnDiscounts(?array $value): self
    {
        $this->instance->setReturnDiscounts($value);
        return $this;
    }

    /**
     * Sets return tips field.
     *
     * @param OrderReturnTip[]|null $value
     */
    public function returnTips(?array $value): self
    {
        $this->instance->setReturnTips($value);
        return $this;
    }

    /**
     * Unsets return tips field.
     */
    public function unsetReturnTips(): self
    {
        $this->instance->unsetReturnTips();
        return $this;
    }

    /**
     * Sets rounding adjustment field.
     *
     * @param OrderRoundingAdjustment|null $value
     */
    public function roundingAdjustment(?OrderRoundingAdjustment $value): self
    {
        $this->instance->setRoundingAdjustment($value);
        return $this;
    }

    /**
     * Sets return amounts field.
     *
     * @param OrderMoneyAmounts|null $value
     */
    public function returnAmounts(?OrderMoneyAmounts $value): self
    {
        $this->instance->setReturnAmounts($value);
        return $this;
    }

    /**
     * Initializes a new Order Return object.
     */
    public function build(): OrderReturn
    {
        return CoreHelper::clone($this->instance);
    }
}
