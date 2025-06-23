<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityDisputeDetail;

/**
 * Builder for model PaymentBalanceActivityDisputeDetail
 *
 * @see PaymentBalanceActivityDisputeDetail
 */
class PaymentBalanceActivityDisputeDetailBuilder
{
    /**
     * @var PaymentBalanceActivityDisputeDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityDisputeDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Dispute Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityDisputeDetail());
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
     * Sets dispute id field.
     *
     * @param string|null $value
     */
    public function disputeId(?string $value): self
    {
        $this->instance->setDisputeId($value);
        return $this;
    }

    /**
     * Unsets dispute id field.
     */
    public function unsetDisputeId(): self
    {
        $this->instance->unsetDisputeId();
        return $this;
    }

    /**
     * Initializes a new Payment Balance Activity Dispute Detail object.
     */
    public function build(): PaymentBalanceActivityDisputeDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
