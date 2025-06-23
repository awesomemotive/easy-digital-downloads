<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityThirdPartyFeeRefundDetail;

/**
 * Builder for model PaymentBalanceActivityThirdPartyFeeRefundDetail
 *
 * @see PaymentBalanceActivityThirdPartyFeeRefundDetail
 */
class PaymentBalanceActivityThirdPartyFeeRefundDetailBuilder
{
    /**
     * @var PaymentBalanceActivityThirdPartyFeeRefundDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityThirdPartyFeeRefundDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Third Party Fee Refund Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityThirdPartyFeeRefundDetail());
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
     * Sets refund id field.
     *
     * @param string|null $value
     */
    public function refundId(?string $value): self
    {
        $this->instance->setRefundId($value);
        return $this;
    }

    /**
     * Unsets refund id field.
     */
    public function unsetRefundId(): self
    {
        $this->instance->unsetRefundId();
        return $this;
    }

    /**
     * Initializes a new Payment Balance Activity Third Party Fee Refund Detail object.
     */
    public function build(): PaymentBalanceActivityThirdPartyFeeRefundDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
