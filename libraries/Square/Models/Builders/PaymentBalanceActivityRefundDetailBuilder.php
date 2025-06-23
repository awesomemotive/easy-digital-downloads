<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityRefundDetail;

/**
 * Builder for model PaymentBalanceActivityRefundDetail
 *
 * @see PaymentBalanceActivityRefundDetail
 */
class PaymentBalanceActivityRefundDetailBuilder
{
    /**
     * @var PaymentBalanceActivityRefundDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityRefundDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Refund Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityRefundDetail());
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
     * Initializes a new Payment Balance Activity Refund Detail object.
     */
    public function build(): PaymentBalanceActivityRefundDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
