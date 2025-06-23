<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Invoice;
use EDD\Vendor\Square\Models\InvoiceAcceptedPaymentMethods;
use EDD\Vendor\Square\Models\InvoiceAttachment;
use EDD\Vendor\Square\Models\InvoiceCustomField;
use EDD\Vendor\Square\Models\InvoicePaymentRequest;
use EDD\Vendor\Square\Models\InvoiceRecipient;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model Invoice
 *
 * @see Invoice
 */
class InvoiceBuilder
{
    /**
     * @var Invoice
     */
    private $instance;

    private function __construct(Invoice $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Invoice Builder object.
     */
    public static function init(): self
    {
        return new self(new Invoice());
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
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
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
     * Unsets order id field.
     */
    public function unsetOrderId(): self
    {
        $this->instance->unsetOrderId();
        return $this;
    }

    /**
     * Sets primary recipient field.
     *
     * @param InvoiceRecipient|null $value
     */
    public function primaryRecipient(?InvoiceRecipient $value): self
    {
        $this->instance->setPrimaryRecipient($value);
        return $this;
    }

    /**
     * Sets payment requests field.
     *
     * @param InvoicePaymentRequest[]|null $value
     */
    public function paymentRequests(?array $value): self
    {
        $this->instance->setPaymentRequests($value);
        return $this;
    }

    /**
     * Unsets payment requests field.
     */
    public function unsetPaymentRequests(): self
    {
        $this->instance->unsetPaymentRequests();
        return $this;
    }

    /**
     * Sets delivery method field.
     *
     * @param string|null $value
     */
    public function deliveryMethod(?string $value): self
    {
        $this->instance->setDeliveryMethod($value);
        return $this;
    }

    /**
     * Sets invoice number field.
     *
     * @param string|null $value
     */
    public function invoiceNumber(?string $value): self
    {
        $this->instance->setInvoiceNumber($value);
        return $this;
    }

    /**
     * Unsets invoice number field.
     */
    public function unsetInvoiceNumber(): self
    {
        $this->instance->unsetInvoiceNumber();
        return $this;
    }

    /**
     * Sets title field.
     *
     * @param string|null $value
     */
    public function title(?string $value): self
    {
        $this->instance->setTitle($value);
        return $this;
    }

    /**
     * Unsets title field.
     */
    public function unsetTitle(): self
    {
        $this->instance->unsetTitle();
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
     * Sets scheduled at field.
     *
     * @param string|null $value
     */
    public function scheduledAt(?string $value): self
    {
        $this->instance->setScheduledAt($value);
        return $this;
    }

    /**
     * Unsets scheduled at field.
     */
    public function unsetScheduledAt(): self
    {
        $this->instance->unsetScheduledAt();
        return $this;
    }

    /**
     * Sets public url field.
     *
     * @param string|null $value
     */
    public function publicUrl(?string $value): self
    {
        $this->instance->setPublicUrl($value);
        return $this;
    }

    /**
     * Sets next payment amount money field.
     *
     * @param Money|null $value
     */
    public function nextPaymentAmountMoney(?Money $value): self
    {
        $this->instance->setNextPaymentAmountMoney($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets timezone field.
     *
     * @param string|null $value
     */
    public function timezone(?string $value): self
    {
        $this->instance->setTimezone($value);
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
     * Sets accepted payment methods field.
     *
     * @param InvoiceAcceptedPaymentMethods|null $value
     */
    public function acceptedPaymentMethods(?InvoiceAcceptedPaymentMethods $value): self
    {
        $this->instance->setAcceptedPaymentMethods($value);
        return $this;
    }

    /**
     * Sets custom fields field.
     *
     * @param InvoiceCustomField[]|null $value
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
     * Sets subscription id field.
     *
     * @param string|null $value
     */
    public function subscriptionId(?string $value): self
    {
        $this->instance->setSubscriptionId($value);
        return $this;
    }

    /**
     * Sets sale or service date field.
     *
     * @param string|null $value
     */
    public function saleOrServiceDate(?string $value): self
    {
        $this->instance->setSaleOrServiceDate($value);
        return $this;
    }

    /**
     * Unsets sale or service date field.
     */
    public function unsetSaleOrServiceDate(): self
    {
        $this->instance->unsetSaleOrServiceDate();
        return $this;
    }

    /**
     * Sets payment conditions field.
     *
     * @param string|null $value
     */
    public function paymentConditions(?string $value): self
    {
        $this->instance->setPaymentConditions($value);
        return $this;
    }

    /**
     * Unsets payment conditions field.
     */
    public function unsetPaymentConditions(): self
    {
        $this->instance->unsetPaymentConditions();
        return $this;
    }

    /**
     * Sets store payment method enabled field.
     *
     * @param bool|null $value
     */
    public function storePaymentMethodEnabled(?bool $value): self
    {
        $this->instance->setStorePaymentMethodEnabled($value);
        return $this;
    }

    /**
     * Unsets store payment method enabled field.
     */
    public function unsetStorePaymentMethodEnabled(): self
    {
        $this->instance->unsetStorePaymentMethodEnabled();
        return $this;
    }

    /**
     * Sets attachments field.
     *
     * @param InvoiceAttachment[]|null $value
     */
    public function attachments(?array $value): self
    {
        $this->instance->setAttachments($value);
        return $this;
    }

    /**
     * Initializes a new Invoice object.
     */
    public function build(): Invoice
    {
        return CoreHelper::clone($this->instance);
    }
}
