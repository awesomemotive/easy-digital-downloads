<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityAutomaticSavingsReversedDetail;

/**
 * Builder for model PaymentBalanceActivityAutomaticSavingsReversedDetail
 *
 * @see PaymentBalanceActivityAutomaticSavingsReversedDetail
 */
class PaymentBalanceActivityAutomaticSavingsReversedDetailBuilder
{
    /**
     * @var PaymentBalanceActivityAutomaticSavingsReversedDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityAutomaticSavingsReversedDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Automatic Savings Reversed Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityAutomaticSavingsReversedDetail());
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
     * Unsets payment id field.
     */
    public function unsetPaymentId(): self
    {
        $this->instance->unsetPaymentId();
        return $this;
    }

    /**
     * Sets payout id field.
     *
     * @param string|null $value
     */
    public function payoutId(?string $value): self
    {
        $this->instance->setPayoutId($value);
        return $this;
    }

    /**
     * Unsets payout id field.
     */
    public function unsetPayoutId(): self
    {
        $this->instance->unsetPayoutId();
        return $this;
    }

    /**
     * Initializes a new Payment Balance Activity Automatic Savings Reversed Detail object.
     */
    public function build(): PaymentBalanceActivityAutomaticSavingsReversedDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
