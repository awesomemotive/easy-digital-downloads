<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\InvoiceRecipient;
use EDD\Vendor\Square\Models\InvoiceRecipientTaxIds;

/**
 * Builder for model InvoiceRecipient
 *
 * @see InvoiceRecipient
 */
class InvoiceRecipientBuilder
{
    /**
     * @var InvoiceRecipient
     */
    private $instance;

    private function __construct(InvoiceRecipient $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Invoice Recipient Builder object.
     */
    public static function init(): self
    {
        return new self(new InvoiceRecipient());
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
     * Sets given name field.
     *
     * @param string|null $value
     */
    public function givenName(?string $value): self
    {
        $this->instance->setGivenName($value);
        return $this;
    }

    /**
     * Sets family name field.
     *
     * @param string|null $value
     */
    public function familyName(?string $value): self
    {
        $this->instance->setFamilyName($value);
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
     * Sets company name field.
     *
     * @param string|null $value
     */
    public function companyName(?string $value): self
    {
        $this->instance->setCompanyName($value);
        return $this;
    }

    /**
     * Sets tax ids field.
     *
     * @param InvoiceRecipientTaxIds|null $value
     */
    public function taxIds(?InvoiceRecipientTaxIds $value): self
    {
        $this->instance->setTaxIds($value);
        return $this;
    }

    /**
     * Initializes a new Invoice Recipient object.
     */
    public function build(): InvoiceRecipient
    {
        return CoreHelper::clone($this->instance);
    }
}
