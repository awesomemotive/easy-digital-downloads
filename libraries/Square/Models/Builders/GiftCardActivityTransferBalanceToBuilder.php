<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityTransferBalanceTo;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model GiftCardActivityTransferBalanceTo
 *
 * @see GiftCardActivityTransferBalanceTo
 */
class GiftCardActivityTransferBalanceToBuilder
{
    /**
     * @var GiftCardActivityTransferBalanceTo
     */
    private $instance;

    private function __construct(GiftCardActivityTransferBalanceTo $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Transfer Balance To Builder object.
     *
     * @param string $transferFromGiftCardId
     * @param Money $amountMoney
     */
    public static function init(string $transferFromGiftCardId, Money $amountMoney): self
    {
        return new self(new GiftCardActivityTransferBalanceTo($transferFromGiftCardId, $amountMoney));
    }

    /**
     * Initializes a new Gift Card Activity Transfer Balance To object.
     */
    public function build(): GiftCardActivityTransferBalanceTo
    {
        return CoreHelper::clone($this->instance);
    }
}
