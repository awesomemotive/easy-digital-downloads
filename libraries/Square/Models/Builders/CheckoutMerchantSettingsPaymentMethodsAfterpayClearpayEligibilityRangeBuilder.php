<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange
 *
 * @see CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange
 */
class CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRangeBuilder
{
    /**
     * @var CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange
     */
    private $instance;

    private function __construct(CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Merchant Settings Payment Methods Afterpay Clearpay Eligibility Range
     * Builder object.
     *
     * @param Money $min
     * @param Money $max
     */
    public static function init(Money $min, Money $max): self
    {
        return new self(new CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange($min, $max));
    }

    /**
     * Initializes a new Checkout Merchant Settings Payment Methods Afterpay Clearpay Eligibility Range
     * object.
     */
    public function build(): CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange
    {
        return CoreHelper::clone($this->instance);
    }
}
