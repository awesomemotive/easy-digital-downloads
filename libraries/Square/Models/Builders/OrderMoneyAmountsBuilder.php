<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderMoneyAmounts;

/**
 * Builder for model OrderMoneyAmounts
 *
 * @see OrderMoneyAmounts
 */
class OrderMoneyAmountsBuilder
{
    /**
     * @var OrderMoneyAmounts
     */
    private $instance;

    private function __construct(OrderMoneyAmounts $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Money Amounts Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderMoneyAmounts());
    }

    /**
     * Sets total money field.
     *
     * @param Money|null $value
     */
    public function totalMoney(?Money $value): self
    {
        $this->instance->setTotalMoney($value);
        return $this;
    }

    /**
     * Sets tax money field.
     *
     * @param Money|null $value
     */
    public function taxMoney(?Money $value): self
    {
        $this->instance->setTaxMoney($value);
        return $this;
    }

    /**
     * Sets discount money field.
     *
     * @param Money|null $value
     */
    public function discountMoney(?Money $value): self
    {
        $this->instance->setDiscountMoney($value);
        return $this;
    }

    /**
     * Sets tip money field.
     *
     * @param Money|null $value
     */
    public function tipMoney(?Money $value): self
    {
        $this->instance->setTipMoney($value);
        return $this;
    }

    /**
     * Sets service charge money field.
     *
     * @param Money|null $value
     */
    public function serviceChargeMoney(?Money $value): self
    {
        $this->instance->setServiceChargeMoney($value);
        return $this;
    }

    /**
     * Initializes a new Order Money Amounts object.
     */
    public function build(): OrderMoneyAmounts
    {
        return CoreHelper::clone($this->instance);
    }
}
