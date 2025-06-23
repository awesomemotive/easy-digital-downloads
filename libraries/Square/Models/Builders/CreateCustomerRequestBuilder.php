<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\CreateCustomerRequest;
use EDD\Vendor\Square\Models\CustomerTaxIds;

/**
 * Builder for model CreateCustomerRequest
 *
 * @see CreateCustomerRequest
 */
class CreateCustomerRequestBuilder
{
    /**
     * @var CreateCustomerRequest
     */
    private $instance;

    private function __construct(CreateCustomerRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Customer Request Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateCustomerRequest());
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
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
     * Sets nickname field.
     *
     * @param string|null $value
     */
    public function nickname(?string $value): self
    {
        $this->instance->setNickname($value);
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
     * Sets reference id field.
     *
     * @param string|null $value
     */
    public function referenceId(?string $value): self
    {
        $this->instance->setReferenceId($value);
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
     * Sets birthday field.
     *
     * @param string|null $value
     */
    public function birthday(?string $value): self
    {
        $this->instance->setBirthday($value);
        return $this;
    }

    /**
     * Sets tax ids field.
     *
     * @param CustomerTaxIds|null $value
     */
    public function taxIds(?CustomerTaxIds $value): self
    {
        $this->instance->setTaxIds($value);
        return $this;
    }

    /**
     * Initializes a new Create Customer Request object.
     */
    public function build(): CreateCustomerRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
