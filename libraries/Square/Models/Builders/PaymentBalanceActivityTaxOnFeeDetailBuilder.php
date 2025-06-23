<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityTaxOnFeeDetail;

/**
 * Builder for model PaymentBalanceActivityTaxOnFeeDetail
 *
 * @see PaymentBalanceActivityTaxOnFeeDetail
 */
class PaymentBalanceActivityTaxOnFeeDetailBuilder
{
    /**
     * @var PaymentBalanceActivityTaxOnFeeDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityTaxOnFeeDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Tax On Fee Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityTaxOnFeeDetail());
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
     * Sets tax rate description field.
     *
     * @param string|null $value
     */
    public function taxRateDescription(?string $value): self
    {
        $this->instance->setTaxRateDescription($value);
        return $this;
    }

    /**
     * Unsets tax rate description field.
     */
    public function unsetTaxRateDescription(): self
    {
        $this->instance->unsetTaxRateDescription();
        return $this;
    }

    /**
     * Initializes a new Payment Balance Activity Tax On Fee Detail object.
     */
    public function build(): PaymentBalanceActivityTaxOnFeeDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
