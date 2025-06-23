<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityRedeem;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model GiftCardActivityRedeem
 *
 * @see GiftCardActivityRedeem
 */
class GiftCardActivityRedeemBuilder
{
    /**
     * @var GiftCardActivityRedeem
     */
    private $instance;

    private function __construct(GiftCardActivityRedeem $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Redeem Builder object.
     *
     * @param Money $amountMoney
     */
    public static function init(Money $amountMoney): self
    {
        return new self(new GiftCardActivityRedeem($amountMoney));
    }

    /**
     * Sets payment id field.
     *
     * @param string|null $value
     */
    public function paymentId(?string $value): self
    {
        $this->instance->setPaymentId($value);
        return $this;
    }

    /**
     * Sets reference id field.
     *
     * @param string|null $value
     */
    public function referenceId(?string $value): self
    {
        $this->instance->setReferenceId($value);
        return $this;
    }

    /**
     * Unsets reference id field.
     */
    public function unsetReferenceId(): self
    {
        $this->instance->unsetReferenceId();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Initializes a new Gift Card Activity Redeem object.
     */
    public function build(): GiftCardActivityRedeem
    {
        return CoreHelper::clone($this->instance);
    }
}
