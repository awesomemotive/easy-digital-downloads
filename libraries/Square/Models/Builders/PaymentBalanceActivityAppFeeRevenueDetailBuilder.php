<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityAppFeeRevenueDetail;

/**
 * Builder for model PaymentBalanceActivityAppFeeRevenueDetail
 *
 * @see PaymentBalanceActivityAppFeeRevenueDetail
 */
class PaymentBalanceActivityAppFeeRevenueDetailBuilder
{
    /**
     * @var PaymentBalanceActivityAppFeeRevenueDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityAppFeeRevenueDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity App Fee Revenue Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityAppFeeRevenueDetail());
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
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Initializes a new Payment Balance Activity App Fee Revenue Detail object.
     */
    public function build(): PaymentBalanceActivityAppFeeRevenueDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
