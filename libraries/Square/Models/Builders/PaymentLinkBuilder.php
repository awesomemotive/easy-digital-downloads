<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutOptions;
use EDD\Vendor\Square\Models\PaymentLink;
use EDD\Vendor\Square\Models\PrePopulatedData;

/**
 * Builder for model PaymentLink
 *
 * @see PaymentLink
 */
class PaymentLinkBuilder
{
    /**
     * @var PaymentLink
     */
    private $instance;

    private function __construct(PaymentLink $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Link Builder object.
     *
     * @param int $version
     */
    public static function init(int $version): self
    {
        return new self(new PaymentLink($version));
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
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Unsets description field.
     */
    public function unsetDescription(): self
    {
        $this->instance->unsetDescription();
        return $this;
    }

    /**
     * Sets order id field.
     *
     * @param string|null $value
     */
    public function orderId(?string $value): self
    {
        $this->instance->setOrderId($value);
        return $this;
    }

    /**
     * Sets checkout options field.
     *
     * @param CheckoutOptions|null $value
     */
    public function checkoutOptions(?CheckoutOptions $value): self
    {
        $this->instance->setCheckoutOptions($value);
        return $this;
    }

    /**
     * Sets pre populated data field.
     *
     * @param PrePopulatedData|null $value
     */
    public function prePopulatedData(?PrePopulatedData $value): self
    {
        $this->instance->setPrePopulatedData($value);
        return $this;
    }

    /**
     * Sets url field.
     *
     * @param string|null $value
     */
    public function url(?string $value): self
    {
        $this->instance->setUrl($value);
        return $this;
    }

    /**
     * Sets long url field.
     *
     * @param string|null $value
     */
    public function longUrl(?string $value): self
    {
        $this->instance->setLongUrl($value);
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
     * Sets payment note field.
     *
     * @param string|null $value
     */
    public function paymentNote(?string $value): self
    {
        $this->instance->setPaymentNote($value);
        return $this;
    }

    /**
     * Unsets payment note field.
     */
    public function unsetPaymentNote(): self
    {
        $this->instance->unsetPaymentNote();
        return $this;
    }

    /**
     * Initializes a new Payment Link object.
     */
    public function build(): PaymentLink
    {
        return CoreHelper::clone($this->instance);
    }
}
