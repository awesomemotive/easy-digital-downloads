<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityOtherAdjustmentDetail;

/**
 * Builder for model PaymentBalanceActivityOtherAdjustmentDetail
 *
 * @see PaymentBalanceActivityOtherAdjustmentDetail
 */
class PaymentBalanceActivityOtherAdjustmentDetailBuilder
{
    /**
     * @var PaymentBalanceActivityOtherAdjustmentDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityOtherAdjustmentDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Other Adjustment Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityOtherAdjustmentDetail());
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
     * Initializes a new Payment Balance Activity Other Adjustment Detail object.
     */
    public function build(): PaymentBalanceActivityOtherAdjustmentDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
