<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityAdjustDecrement;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model GiftCardActivityAdjustDecrement
 *
 * @see GiftCardActivityAdjustDecrement
 */
class GiftCardActivityAdjustDecrementBuilder
{
    /**
     * @var GiftCardActivityAdjustDecrement
     */
    private $instance;

    private function __construct(GiftCardActivityAdjustDecrement $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Adjust Decrement Builder object.
     *
     * @param Money $amountMoney
     * @param string $reason
     */
    public static function init(Money $amountMoney, string $reason): self
    {
        return new self(new GiftCardActivityAdjustDecrement($amountMoney, $reason));
    }

    /**
     * Initializes a new Gift Card Activity Adjust Decrement object.
     */
    public function build(): GiftCardActivityAdjustDecrement
    {
        return CoreHelper::clone($this->instance);
    }
}
