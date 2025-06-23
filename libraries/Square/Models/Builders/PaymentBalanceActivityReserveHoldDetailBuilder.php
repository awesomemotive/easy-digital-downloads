<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityReserveHoldDetail;

/**
 * Builder for model PaymentBalanceActivityReserveHoldDetail
 *
 * @see PaymentBalanceActivityReserveHoldDetail
 */
class PaymentBalanceActivityReserveHoldDetailBuilder
{
    /**
     * @var PaymentBalanceActivityReserveHoldDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityReserveHoldDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Reserve Hold Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityReserveHoldDetail());
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
     * Initializes a new Payment Balance Activity Reserve Hold Detail object.
     */
    public function build(): PaymentBalanceActivityReserveHoldDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
