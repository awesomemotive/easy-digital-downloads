<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityDepositFeeReversedDetail;

/**
 * Builder for model PaymentBalanceActivityDepositFeeReversedDetail
 *
 * @see PaymentBalanceActivityDepositFeeReversedDetail
 */
class PaymentBalanceActivityDepositFeeReversedDetailBuilder
{
    /**
     * @var PaymentBalanceActivityDepositFeeReversedDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityDepositFeeReversedDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Deposit Fee Reversed Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityDepositFeeReversedDetail());
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
     * Initializes a new Payment Balance Activity Deposit Fee Reversed Detail object.
     */
    public function build(): PaymentBalanceActivityDepositFeeReversedDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
