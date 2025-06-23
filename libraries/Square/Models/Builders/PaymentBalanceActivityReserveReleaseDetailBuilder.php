<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityReserveReleaseDetail;

/**
 * Builder for model PaymentBalanceActivityReserveReleaseDetail
 *
 * @see PaymentBalanceActivityReserveReleaseDetail
 */
class PaymentBalanceActivityReserveReleaseDetailBuilder
{
    /**
     * @var PaymentBalanceActivityReserveReleaseDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityReserveReleaseDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Reserve Release Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityReserveReleaseDetail());
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
     * Initializes a new Payment Balance Activity Reserve Release Detail object.
     */
    public function build(): PaymentBalanceActivityReserveReleaseDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
