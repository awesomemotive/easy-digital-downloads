<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\InvoiceAcceptedPaymentMethods;

/**
 * Builder for model InvoiceAcceptedPaymentMethods
 *
 * @see InvoiceAcceptedPaymentMethods
 */
class InvoiceAcceptedPaymentMethodsBuilder
{
    /**
     * @var InvoiceAcceptedPaymentMethods
     */
    private $instance;

    private function __construct(InvoiceAcceptedPaymentMethods $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Invoice Accepted Payment Methods Builder object.
     */
    public static function init(): self
    {
        return new self(new InvoiceAcceptedPaymentMethods());
    }

    /**
     * Sets card field.
     *
     * @param bool|null $value
     */
    public function card(?bool $value): self
    {
        $this->instance->setCard($value);
        return $this;
    }

    /**
     * Unsets card field.
     */
    public function unsetCard(): self
    {
        $this->instance->unsetCard();
        return $this;
    }

    /**
     * Sets square gift card field.
     *
     * @param bool|null $value
     */
    public function squareGiftCard(?bool $value): self
    {
        $this->instance->setSquareGiftCard($value);
        return $this;
    }

    /**
     * Unsets square gift card field.
     */
    public function unsetSquareGiftCard(): self
    {
        $this->instance->unsetSquareGiftCard();
        return $this;
    }

    /**
     * Sets bank account field.
     *
     * @param bool|null $value
     */
    public function bankAccount(?bool $value): self
    {
        $this->instance->setBankAccount($value);
        return $this;
    }

    /**
     * Unsets bank account field.
     */
    public function unsetBankAccount(): self
    {
        $this->instance->unsetBankAccount();
        return $this;
    }

    /**
     * Sets buy now pay later field.
     *
     * @param bool|null $value
     */
    public function buyNowPayLater(?bool $value): self
    {
        $this->instance->setBuyNowPayLater($value);
        return $this;
    }

    /**
     * Unsets buy now pay later field.
     */
    public function unsetBuyNowPayLater(): self
    {
        $this->instance->unsetBuyNowPayLater();
        return $this;
    }

    /**
     * Sets cash app pay field.
     *
     * @param bool|null $value
     */
    public function cashAppPay(?bool $value): self
    {
        $this->instance->setCashAppPay($value);
        return $this;
    }

    /**
     * Unsets cash app pay field.
     */
    public function unsetCashAppPay(): self
    {
        $this->instance->unsetCashAppPay();
        return $this;
    }

    /**
     * Initializes a new Invoice Accepted Payment Methods object.
     */
    public function build(): InvoiceAcceptedPaymentMethods
    {
        return CoreHelper::clone($this->instance);
    }
}
