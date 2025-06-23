<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ACHDetails;
use EDD\Vendor\Square\Models\BankAccountPaymentDetails;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BankAccountPaymentDetails
 *
 * @see BankAccountPaymentDetails
 */
class BankAccountPaymentDetailsBuilder
{
    /**
     * @var BankAccountPaymentDetails
     */
    private $instance;

    private function __construct(BankAccountPaymentDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bank Account Payment Details Builder object.
     */
    public static function init(): self
    {
        return new self(new BankAccountPaymentDetails());
    }

    /**
     * Sets bank name field.
     *
     * @param string|null $value
     */
    public function bankName(?string $value): self
    {
        $this->instance->setBankName($value);
        return $this;
    }

    /**
     * Unsets bank name field.
     */
    public function unsetBankName(): self
    {
        $this->instance->unsetBankName();
        return $this;
    }

    /**
     * Sets transfer type field.
     *
     * @param string|null $value
     */
    public function transferType(?string $value): self
    {
        $this->instance->setTransferType($value);
        return $this;
    }

    /**
     * Unsets transfer type field.
     */
    public function unsetTransferType(): self
    {
        $this->instance->unsetTransferType();
        return $this;
    }

    /**
     * Sets account ownership type field.
     *
     * @param string|null $value
     */
    public function accountOwnershipType(?string $value): self
    {
        $this->instance->setAccountOwnershipType($value);
        return $this;
    }

    /**
     * Unsets account ownership type field.
     */
    public function unsetAccountOwnershipType(): self
    {
        $this->instance->unsetAccountOwnershipType();
        return $this;
    }

    /**
     * Sets fingerprint field.
     *
     * @param string|null $value
     */
    public function fingerprint(?string $value): self
    {
        $this->instance->setFingerprint($value);
        return $this;
    }

    /**
     * Unsets fingerprint field.
     */
    public function unsetFingerprint(): self
    {
        $this->instance->unsetFingerprint();
        return $this;
    }

    /**
     * Sets country field.
     *
     * @param string|null $value
     */
    public function country(?string $value): self
    {
        $this->instance->setCountry($value);
        return $this;
    }

    /**
     * Unsets country field.
     */
    public function unsetCountry(): self
    {
        $this->instance->unsetCountry();
        return $this;
    }

    /**
     * Sets statement description field.
     *
     * @param string|null $value
     */
    public function statementDescription(?string $value): self
    {
        $this->instance->setStatementDescription($value);
        return $this;
    }

    /**
     * Unsets statement description field.
     */
    public function unsetStatementDescription(): self
    {
        $this->instance->unsetStatementDescription();
        return $this;
    }

    /**
     * Sets ach details field.
     *
     * @param ACHDetails|null $value
     */
    public function achDetails(?ACHDetails $value): self
    {
        $this->instance->setAchDetails($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Unsets errors field.
     */
    public function unsetErrors(): self
    {
        $this->instance->unsetErrors();
        return $this;
    }

    /**
     * Initializes a new Bank Account Payment Details object.
     */
    public function build(): BankAccountPaymentDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
