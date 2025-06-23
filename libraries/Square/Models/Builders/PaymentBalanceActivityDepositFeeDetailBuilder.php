<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityDepositFeeDetail;

/**
 * Builder for model PaymentBalanceActivityDepositFeeDetail
 *
 * @see PaymentBalanceActivityDepositFeeDetail
 */
class PaymentBalanceActivityDepositFeeDetailBuilder
{
    /**
     * @var PaymentBalanceActivityDepositFeeDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityDepositFeeDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Deposit Fee Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityDepositFeeDetail());
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
     * Initializes a new Payment Balance Activity Deposit Fee Detail object.
     */
    public function build(): PaymentBalanceActivityDepositFeeDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
