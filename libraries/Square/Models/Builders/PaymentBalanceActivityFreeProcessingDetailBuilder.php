<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityFreeProcessingDetail;

/**
 * Builder for model PaymentBalanceActivityFreeProcessingDetail
 *
 * @see PaymentBalanceActivityFreeProcessingDetail
 */
class PaymentBalanceActivityFreeProcessingDetailBuilder
{
    /**
     * @var PaymentBalanceActivityFreeProcessingDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityFreeProcessingDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Free Processing Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityFreeProcessingDetail());
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
     * Initializes a new Payment Balance Activity Free Processing Detail object.
     */
    public function build(): PaymentBalanceActivityFreeProcessingDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
