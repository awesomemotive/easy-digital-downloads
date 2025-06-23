<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\OrderFulfillmentRecipient;

/**
 * Builder for model OrderFulfillmentRecipient
 *
 * @see OrderFulfillmentRecipient
 */
class OrderFulfillmentRecipientBuilder
{
    /**
     * @var OrderFulfillmentRecipient
     */
    private $instance;

    private function __construct(OrderFulfillmentRecipient $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Fulfillment Recipient Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderFulfillmentRecipient());
    }

    /**
     * Sets customer id field.
     *
     * @param string|null $value
     */
    public function customerId(?string $value): self
    {
        $this->instance->setCustomerId($value);
        return $this;
    }

    /**
     * Unsets customer id field.
     */
    public function unsetCustomerId(): self
    {
        $this->instance->unsetCustomerId();
        return $this;
    }

    /**
     * Sets display name field.
     *
     * @param string|null $value
     */
    public function displayName(?string $value): self
    {
        $this->instance->setDisplayName($value);
        return $this;
    }

    /**
     * Unsets display name field.
     */
    public function unsetDisplayName(): self
    {
        $this->instance->unsetDisplayName();
        return $this;
    }

    /**
     * Sets email address field.
     *
     * @param string|null $value
     */
    public function emailAddress(?string $value): self
    {
        $this->instance->setEmailAddress($value);
        return $this;
    }

    /**
     * Unsets email address field.
     */
    public function unsetEmailAddress(): self
    {
        $this->instance->unsetEmailAddress();
        return $this;
    }

    /**
     * Sets phone number field.
     *
     * @param string|null $value
     */
    public function phoneNumber(?string $value): self
    {
        $this->instance->setPhoneNumber($value);
        return $this;
    }

    /**
     * Unsets phone number field.
     */
    public function unsetPhoneNumber(): self
    {
        $this->instance->unsetPhoneNumber();
        return $this;
    }

    /**
     * Sets address field.
     *
     * @param Address|null $value
     */
    public function address(?Address $value): self
    {
        $this->instance->setAddress($value);
        return $this;
    }

    /**
     * Initializes a new Order Fulfillment Recipient object.
     */
    public function build(): OrderFulfillmentRecipient
    {
        return CoreHelper::clone($this->instance);
    }
}
