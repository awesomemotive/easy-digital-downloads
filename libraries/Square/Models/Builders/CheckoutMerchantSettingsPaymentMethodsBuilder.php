<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutMerchantSettingsPaymentMethods;
use EDD\Vendor\Square\Models\CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay;
use EDD\Vendor\Square\Models\CheckoutMerchantSettingsPaymentMethodsPaymentMethod;

/**
 * Builder for model CheckoutMerchantSettingsPaymentMethods
 *
 * @see CheckoutMerchantSettingsPaymentMethods
 */
class CheckoutMerchantSettingsPaymentMethodsBuilder
{
    /**
     * @var CheckoutMerchantSettingsPaymentMethods
     */
    private $instance;

    private function __construct(CheckoutMerchantSettingsPaymentMethods $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Merchant Settings Payment Methods Builder object.
     */
    public static function init(): self
    {
        return new self(new CheckoutMerchantSettingsPaymentMethods());
    }

    /**
     * Sets apple pay field.
     *
     * @param CheckoutMerchantSettingsPaymentMethodsPaymentMethod|null $value
     */
    public function applePay(?CheckoutMerchantSettingsPaymentMethodsPaymentMethod $value): self
    {
        $this->instance->setApplePay($value);
        return $this;
    }

    /**
     * Sets google pay field.
     *
     * @param CheckoutMerchantSettingsPaymentMethodsPaymentMethod|null $value
     */
    public function googlePay(?CheckoutMerchantSettingsPaymentMethodsPaymentMethod $value): self
    {
        $this->instance->setGooglePay($value);
        return $this;
    }

    /**
     * Sets cash app field.
     *
     * @param CheckoutMerchantSettingsPaymentMethodsPaymentMethod|null $value
     */
    public function cashApp(?CheckoutMerchantSettingsPaymentMethodsPaymentMethod $value): self
    {
        $this->instance->setCashApp($value);
        return $this;
    }

    /**
     * Sets afterpay clearpay field.
     *
     * @param CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay|null $value
     */
    public function afterpayClearpay(?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay $value): self
    {
        $this->instance->setAfterpayClearpay($value);
        return $this;
    }

    /**
     * Initializes a new Checkout Merchant Settings Payment Methods object.
     */
    public function build(): CheckoutMerchantSettingsPaymentMethods
    {
        return CoreHelper::clone($this->instance);
    }
}
