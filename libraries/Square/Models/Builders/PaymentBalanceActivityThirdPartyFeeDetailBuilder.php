<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaymentBalanceActivityThirdPartyFeeDetail;

/**
 * Builder for model PaymentBalanceActivityThirdPartyFeeDetail
 *
 * @see PaymentBalanceActivityThirdPartyFeeDetail
 */
class PaymentBalanceActivityThirdPartyFeeDetailBuilder
{
    /**
     * @var PaymentBalanceActivityThirdPartyFeeDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityThirdPartyFeeDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Balance Activity Third Party Fee Detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityThirdPartyFeeDetail());
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
     * Initializes a new Payment Balance Activity Third Party Fee Detail object.
     */
    public function build(): PaymentBalanceActivityThirdPartyFeeDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
