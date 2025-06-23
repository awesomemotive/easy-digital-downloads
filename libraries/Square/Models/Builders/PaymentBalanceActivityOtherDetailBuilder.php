<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityOtherDetail;

/**
 * Builder for model PaymentBalanceActivityOtherDetail
 *
 * @see PaymentBalanceActivityOtherDetail
 */
class PaymentBalanceActivityOtherDetailBuilder
{
    /**
     * @var PaymentBalanceActivityOtherDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityOtherDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Other Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityOtherDetail());
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
     * Initializes a new Payment Balance Activity Other Detail object.
     */
    public function build(): PaymentBalanceActivityOtherDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
