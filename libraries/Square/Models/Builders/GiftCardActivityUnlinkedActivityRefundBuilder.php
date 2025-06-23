<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityUnlinkedActivityRefund;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model GiftCardActivityUnlinkedActivityRefund
 *
 * @see GiftCardActivityUnlinkedActivityRefund
 */
class GiftCardActivityUnlinkedActivityRefundBuilder
{
    /**
     * @var GiftCardActivityUnlinkedActivityRefund
     */
    private $instance;

    private function __construct(GiftCardActivityUnlinkedActivityRefund $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Unlinked Activity Refund Builder object.
     *
     * @param Money $amountMoney
     */
    public static function init(Money $amountMoney): self
    {
        return new self(new GiftCardActivityUnlinkedActivityRefund($amountMoney));
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
     * Initializes a new Gift Card Activity Unlinked Activity Refund object.
     */
    public function build(): GiftCardActivityUnlinkedActivityRefund
    {
        return CoreHelper::clone($this->instance);
    }
}
