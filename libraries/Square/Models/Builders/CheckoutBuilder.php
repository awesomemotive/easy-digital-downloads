<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AdditionalRecipient;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\Checkout;
use EDD\Vendor\Square\Models\Order;

/**
 * Builder for model Checkout
 *
 * @see Checkout
 */
class CheckoutBuilder
{
    /**
     * @var Checkout
     */
    private $instance;

    private function __construct(Checkout $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Checkout Builder object.
     */
    public static function init(): self
    {
        return new self(new Checkout());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets checkout page url field.
     *
     * @param string|null $value
     */
    public function checkoutPageUrl(?string $value): self
    {
        $this->instance->setCheckoutPageUrl($value);
        return $this;
    }

    /**
     * Unsets checkout page url field.
     */
    public function unsetCheckoutPageUrl(): self
    {
        $this->instance->unsetCheckoutPageUrl();
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
     * Sets pre populate buyer email field.
     *
     * @param string|null $value
     */
    public function prePopulateBuyerEmail(?string $value): self
    {
        $this->instance->setPrePopulateBuyerEmail($value);
        return $this;
    }

    /**
     * Unsets pre populate buyer email field.
     */
    public function unsetPrePopulateBuyerEmail(): self
    {
        $this->instance->unsetPrePopulateBuyerEmail();
        return $this;
    }

    /**
     * Sets pre populate shipping address field.
     *
     * @param Address|null $value
     */
    public function prePopulateShippingAddress(?Address $value): self
    {
        $this->instance->setPrePopulateShippingAddress($value);
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
     * Sets order field.
     *
     * @param Order|null $value
     */
    public function order(?Order $value): self
    {
        $this->instance->setOrder($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets additional recipients field.
     *
     * @param AdditionalRecipient[]|null $value
     */
    public function additionalRecipients(?array $value): self
    {
        $this->instance->setAdditionalRecipients($value);
        return $this;
    }

    /**
     * Unsets additional recipients field.
     */
    public function unsetAdditionalRecipients(): self
    {
        $this->instance->unsetAdditionalRecipients();
        return $this;
    }

    /**
     * Initializes a new Checkout object.
     */
    public function build(): Checkout
    {
        return CoreHelper::clone($this->instance);
    }
}
