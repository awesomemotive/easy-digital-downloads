<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AcceptedPaymentMethods;
use EDD\Vendor\Square\Models\CheckoutOptions;
use EDD\Vendor\Square\Models\CustomField;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\ShippingFee;

/**
 * Builder for model CheckoutOptions
 *
 * @see CheckoutOptions
 */
class CheckoutOptionsBuilder
{
    /**
     * @var CheckoutOptions
     */
    private $instance;

    private function __construct(CheckoutOptions $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Options Builder object.
     */
    public static function init(): self
    {
        return new self(new CheckoutOptions());
    }

    /**
     * Sets allow tipping field.
     *
     * @param bool|null $value
     */
    public function allowTipping(?bool $value): self
    {
        $this->instance->setAllowTipping($value);
        return $this;
    }

    /**
     * Unsets allow tipping field.
     */
    public function unsetAllowTipping(): self
    {
        $this->instance->unsetAllowTipping();
        return $this;
    }

    /**
     * Sets custom fields field.
     *
     * @param CustomField[]|null $value
     */
    public function customFields(?array $value): self
    {
        $this->instance->setCustomFields($value);
        return $this;
    }

    /**
     * Unsets custom fields field.
     */
    public function unsetCustomFields(): self
    {
        $this->instance->unsetCustomFields();
        return $this;
    }

    /**
     * Sets subscription plan id field.
     *
     * @param string|null $value
     */
    public function subscriptionPlanId(?string $value): self
    {
        $this->instance->setSubscriptionPlanId($value);
        return $this;
    }

    /**
     * Unsets subscription plan id field.
     */
    public function unsetSubscriptionPlanId(): self
    {
        $this->instance->unsetSubscriptionPlanId();
        return $this;
    }

    /**
     * Sets redirect url field.
     *
     * @param string|null $value
     */
    public function redirectUrl(?string $value): self
    {
        $this->instance->setRedirectUrl($value);
        return $this;
    }

    /**
     * Unsets redirect url field.
     */
    public function unsetRedirectUrl(): self
    {
        $this->instance->unsetRedirectUrl();
        return $this;
    }

    /**
     * Sets merchant support email field.
     *
     * @param string|null $value
     */
    public function merchantSupportEmail(?string $value): self
    {
        $this->instance->setMerchantSupportEmail($value);
        return $this;
    }

    /**
     * Unsets merchant support email field.
     */
    public function unsetMerchantSupportEmail(): self
    {
        $this->instance->unsetMerchantSupportEmail();
        return $this;
    }

    /**
     * Sets ask for shipping address field.
     *
     * @param bool|null $value
     */
    public function askForShippingAddress(?bool $value): self
    {
        $this->instance->setAskForShippingAddress($value);
        return $this;
    }

    /**
     * Unsets ask for shipping address field.
     */
    public function unsetAskForShippingAddress(): self
    {
        $this->instance->unsetAskForShippingAddress();
        return $this;
    }

    /**
     * Sets accepted payment methods field.
     *
     * @param AcceptedPaymentMethods|null $value
     */
    public function acceptedPaymentMethods(?AcceptedPaymentMethods $value): self
    {
        $this->instance->setAcceptedPaymentMethods($value);
        return $this;
    }

    /**
     * Sets app fee money field.
     *
     * @param Money|null $value
     */
    public function appFeeMoney(?Money $value): self
    {
        $this->instance->setAppFeeMoney($value);
        return $this;
    }

    /**
     * Sets shipping fee field.
     *
     * @param ShippingFee|null $value
     */
    public function shippingFee(?ShippingFee $value): self
    {
        $this->instance->setShippingFee($value);
        return $this;
    }

    /**
     * Sets enable coupon field.
     *
     * @param bool|null $value
     */
    public function enableCoupon(?bool $value): self
    {
        $this->instance->setEnableCoupon($value);
        return $this;
    }

    /**
     * Unsets enable coupon field.
     */
    public function unsetEnableCoupon(): self
    {
        $this->instance->unsetEnableCoupon();
        return $this;
    }

    /**
     * Sets enable loyalty field.
     *
     * @param bool|null $value
     */
    public function enableLoyalty(?bool $value): self
    {
        $this->instance->setEnableLoyalty($value);
        return $this;
    }

    /**
     * Unsets enable loyalty field.
     */
    public function unsetEnableLoyalty(): self
    {
        $this->instance->unsetEnableLoyalty();
        return $this;
    }

    /**
     * Initializes a new Checkout Options object.
     */
    public function build(): CheckoutOptions
    {
        return CoreHelper::clone($this->instance);
    }
}
