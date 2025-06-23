<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutMerchantSettings;
use EDD\Vendor\Square\Models\CheckoutMerchantSettingsPaymentMethods;

/**
 * Builder for model CheckoutMerchantSettings
 *
 * @see CheckoutMerchantSettings
 */
class CheckoutMerchantSettingsBuilder
{
    /**
     * @var CheckoutMerchantSettings
     */
    private $instance;

    private function __construct(CheckoutMerchantSettings $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Merchant Settings Builder object.
     */
    public static function init(): self
    {
        return new self(new CheckoutMerchantSettings());
    }

    /**
     * Sets payment methods field.
     *
     * @param CheckoutMerchantSettingsPaymentMethods|null $value
     */
    public function paymentMethods(?CheckoutMerchantSettingsPaymentMethods $value): self
    {
        $this->instance->setPaymentMethods($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Checkout Merchant Settings object.
     */
    public function build(): CheckoutMerchantSettings
    {
        return CoreHelper::clone($this->instance);
    }
}
