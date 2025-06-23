<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\ChargeRequestAdditionalRecipient;
use EDD\Vendor\Square\Models\CreateCheckoutRequest;
use EDD\Vendor\Square\Models\CreateOrderRequest;

/**
 * Builder for model CreateCheckoutRequest
 *
 * @see CreateCheckoutRequest
 */
class CreateCheckoutRequestBuilder
{
    /**
     * @var CreateCheckoutRequest
     */
    private $instance;

    private function __construct(CreateCheckoutRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Checkout Request Builder object.
     *
     * @param string $idempotencyKey
     * @param CreateOrderRequest $order
     */
    public static function init(string $idempotencyKey, CreateOrderRequest $order): self
    {
        return new self(new CreateCheckoutRequest($idempotencyKey, $order));
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
     * Sets additional recipients field.
     *
     * @param ChargeRequestAdditionalRecipient[]|null $value
     */
    public function additionalRecipients(?array $value): self
    {
        $this->instance->setAdditionalRecipients($value);
        return $this;
    }

    /**
     * Sets note field.
     *
     * @param string|null $value
     */
    public function note(?string $value): self
    {
        $this->instance->setNote($value);
        return $this;
    }

    /**
     * Initializes a new Create Checkout Request object.
     */
    public function build(): CreateCheckoutRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
