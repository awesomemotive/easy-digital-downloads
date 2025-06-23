<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Dispute;
use EDD\Vendor\Square\Models\DisputedPayment;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model Dispute
 *
 * @see Dispute
 */
class DisputeBuilder
{
    /**
     * @var Dispute
     */
    private $instance;

    private function __construct(Dispute $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Dispute Builder object.
     */
    public static function init(): self
    {
        return new self(new Dispute());
    }

    /**
     * Sets dispute id field.
     *
     * @param string|null $value
     */
    public function disputeId(?string $value): self
    {
        $this->instance->setDisputeId($value);
        return $this;
    }

    /**
     * Unsets dispute id field.
     */
    public function unsetDisputeId(): self
    {
        $this->instance->unsetDisputeId();
        return $this;
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
     * Sets amount money field.
     *
     * @param Money|null $value
     */
    public function amountMoney(?Money $value): self
    {
        $this->instance->setAmountMoney($value);
        return $this;
    }

    /**
     * Sets reason field.
     *
     * @param string|null $value
     */
    public function reason(?string $value): self
    {
        $this->instance->setReason($value);
        return $this;
    }

    /**
     * Sets state field.
     *
     * @param string|null $value
     */
    public function state(?string $value): self
    {
        $this->instance->setState($value);
        return $this;
    }

    /**
     * Sets due at field.
     *
     * @param string|null $value
     */
    public function dueAt(?string $value): self
    {
        $this->instance->setDueAt($value);
        return $this;
    }

    /**
     * Unsets due at field.
     */
    public function unsetDueAt(): self
    {
        $this->instance->unsetDueAt();
        return $this;
    }

    /**
     * Sets disputed payment field.
     *
     * @param DisputedPayment|null $value
     */
    public function disputedPayment(?DisputedPayment $value): self
    {
        $this->instance->setDisputedPayment($value);
        return $this;
    }

    /**
     * Sets evidence ids field.
     *
     * @param string[]|null $value
     */
    public function evidenceIds(?array $value): self
    {
        $this->instance->setEvidenceIds($value);
        return $this;
    }

    /**
     * Unsets evidence ids field.
     */
    public function unsetEvidenceIds(): self
    {
        $this->instance->unsetEvidenceIds();
        return $this;
    }

    /**
     * Sets card brand field.
     *
     * @param string|null $value
     */
    public function cardBrand(?string $value): self
    {
        $this->instance->setCardBrand($value);
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
     * Sets brand dispute id field.
     *
     * @param string|null $value
     */
    public function brandDisputeId(?string $value): self
    {
        $this->instance->setBrandDisputeId($value);
        return $this;
    }

    /**
     * Unsets brand dispute id field.
     */
    public function unsetBrandDisputeId(): self
    {
        $this->instance->unsetBrandDisputeId();
        return $this;
    }

    /**
     * Sets reported date field.
     *
     * @param string|null $value
     */
    public function reportedDate(?string $value): self
    {
        $this->instance->setReportedDate($value);
        return $this;
    }

    /**
     * Unsets reported date field.
     */
    public function unsetReportedDate(): self
    {
        $this->instance->unsetReportedDate();
        return $this;
    }

    /**
     * Sets reported at field.
     *
     * @param string|null $value
     */
    public function reportedAt(?string $value): self
    {
        $this->instance->setReportedAt($value);
        return $this;
    }

    /**
     * Unsets reported at field.
     */
    public function unsetReportedAt(): self
    {
        $this->instance->unsetReportedAt();
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
     * Initializes a new Dispute object.
     */
    public function build(): Dispute
    {
        return CoreHelper::clone($this->instance);
    }
}
