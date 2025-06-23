<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityHoldAdjustmentDetail;

/**
 * Builder for model PaymentBalanceActivityHoldAdjustmentDetail
 *
 * @see PaymentBalanceActivityHoldAdjustmentDetail
 */
class PaymentBalanceActivityHoldAdjustmentDetailBuilder
{
    /**
     * @var PaymentBalanceActivityHoldAdjustmentDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityHoldAdjustmentDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Hold Adjustment Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityHoldAdjustmentDetail());
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
     * Initializes a new Payment Balance Activity Hold Adjustment Detail object.
     */
    public function build(): PaymentBalanceActivityHoldAdjustmentDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
