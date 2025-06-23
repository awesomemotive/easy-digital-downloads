<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CheckoutMerchantSettingsPaymentMethods implements \JsonSerializable
{
    /**
     * @var CheckoutMerchantSettingsPaymentMethodsPaymentMethod|null
     */
    private $applePay;

    /**
     * @var CheckoutMerchantSettingsPaymentMethodsPaymentMethod|null
     */
    private $googlePay;

    /**
     * @var CheckoutMerchantSettingsPaymentMethodsPaymentMethod|null
     */
    private $cashApp;

    /**
     * @var CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay|null
     */
    private $afterpayClearpay;

    /**
     * Returns Apple Pay.
     * The settings allowed for a payment method.
     */
    public function getApplePay(): ?CheckoutMerchantSettingsPaymentMethodsPaymentMethod
    {
        return $this->applePay;
    }

    /**
     * Sets Apple Pay.
     * The settings allowed for a payment method.
     *
     * @maps apple_pay
     */
    public function setApplePay(?CheckoutMerchantSettingsPaymentMethodsPaymentMethod $applePay): void
    {
        $this->applePay = $applePay;
    }

    /**
     * Returns Google Pay.
     * The settings allowed for a payment method.
     */
    public function getGooglePay(): ?CheckoutMerchantSettingsPaymentMethodsPaymentMethod
    {
        return $this->googlePay;
    }

    /**
     * Sets Google Pay.
     * The settings allowed for a payment method.
     *
     * @maps google_pay
     */
    public function setGooglePay(?CheckoutMerchantSettingsPaymentMethodsPaymentMethod $googlePay): void
    {
        $this->googlePay = $googlePay;
    }

    /**
     * Returns Cash App.
     * The settings allowed for a payment method.
     */
    public function getCashApp(): ?CheckoutMerchantSettingsPaymentMethodsPaymentMethod
    {
        return $this->cashApp;
    }

    /**
     * Sets Cash App.
     * The settings allowed for a payment method.
     *
     * @maps cash_app
     */
    public function setCashApp(?CheckoutMerchantSettingsPaymentMethodsPaymentMethod $cashApp): void
    {
        $this->cashApp = $cashApp;
    }

    /**
     * Returns Afterpay Clearpay.
     * The settings allowed for AfterpayClearpay.
     */
    public function getAfterpayClearpay(): ?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay
    {
        return $this->afterpayClearpay;
    }

    /**
     * Sets Afterpay Clearpay.
     * The settings allowed for AfterpayClearpay.
     *
     * @maps afterpay_clearpay
     */
    public function setAfterpayClearpay(
        ?CheckoutMerchantSettingsPaymentMethodsAfterpayClearpay $afterpayClearpay
    ): void {
        $this->afterpayClearpay = $afterpayClearpay;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (isset($this->applePay)) {
            $json['apple_pay']         = $this->applePay;
        }
        if (isset($this->googlePay)) {
            $json['google_pay']        = $this->googlePay;
        }
        if (isset($this->cashApp)) {
            $json['cash_app']          = $this->cashApp;
        }
        if (isset($this->afterpayClearpay)) {
            $json['afterpay_clearpay'] = $this->afterpayClearpay;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
