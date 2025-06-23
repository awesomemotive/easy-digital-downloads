<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\V1Money;
use EDD\Vendor\Square\Models\V1Tender;

/**
 * Builder for model V1Tender
 *
 * @see V1Tender
 */
class V1TenderBuilder
{
    /**
     * @var V1Tender
     */
    private $instance;

    private function __construct(V1Tender $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 Tender Builder object.
     */
    public static function init(): self
    {
        return new self(new V1Tender());
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
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets employee id field.
     *
     * @param string|null $value
     */
    public function employeeId(?string $value): self
    {
        $this->instance->setEmployeeId($value);
        return $this;
    }

    /**
     * Unsets employee id field.
     */
    public function unsetEmployeeId(): self
    {
        $this->instance->unsetEmployeeId();
        return $this;
    }

    /**
     * Sets receipt url field.
     *
     * @param string|null $value
     */
    public function receiptUrl(?string $value): self
    {
        $this->instance->setReceiptUrl($value);
        return $this;
    }

    /**
     * Unsets receipt url field.
     */
    public function unsetReceiptUrl(): self
    {
        $this->instance->unsetReceiptUrl();
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
     * Sets pan suffix field.
     *
     * @param string|null $value
     */
    public function panSuffix(?string $value): self
    {
        $this->instance->setPanSuffix($value);
        return $this;
    }

    /**
     * Unsets pan suffix field.
     */
    public function unsetPanSuffix(): self
    {
        $this->instance->unsetPanSuffix();
        return $this;
    }

    /**
     * Sets entry method field.
     *
     * @param string|null $value
     */
    public function entryMethod(?string $value): self
    {
        $this->instance->setEntryMethod($value);
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
     * Sets total money field.
     *
     * @param V1Money|null $value
     */
    public function totalMoney(?V1Money $value): self
    {
        $this->instance->setTotalMoney($value);
        return $this;
    }

    /**
     * Sets tendered money field.
     *
     * @param V1Money|null $value
     */
    public function tenderedMoney(?V1Money $value): self
    {
        $this->instance->setTenderedMoney($value);
        return $this;
    }

    /**
     * Sets tendered at field.
     *
     * @param string|null $value
     */
    public function tenderedAt(?string $value): self
    {
        $this->instance->setTenderedAt($value);
        return $this;
    }

    /**
     * Unsets tendered at field.
     */
    public function unsetTenderedAt(): self
    {
        $this->instance->unsetTenderedAt();
        return $this;
    }

    /**
     * Sets settled at field.
     *
     * @param string|null $value
     */
    public function settledAt(?string $value): self
    {
        $this->instance->setSettledAt($value);
        return $this;
    }

    /**
     * Unsets settled at field.
     */
    public function unsetSettledAt(): self
    {
        $this->instance->unsetSettledAt();
        return $this;
    }

    /**
     * Sets change back money field.
     *
     * @param V1Money|null $value
     */
    public function changeBackMoney(?V1Money $value): self
    {
        $this->instance->setChangeBackMoney($value);
        return $this;
    }

    /**
     * Sets refunded money field.
     *
     * @param V1Money|null $value
     */
    public function refundedMoney(?V1Money $value): self
    {
        $this->instance->setRefundedMoney($value);
        return $this;
    }

    /**
     * Sets is exchange field.
     *
     * @param bool|null $value
     */
    public function isExchange(?bool $value): self
    {
        $this->instance->setIsExchange($value);
        return $this;
    }

    /**
     * Unsets is exchange field.
     */
    public function unsetIsExchange(): self
    {
        $this->instance->unsetIsExchange();
        return $this;
    }

    /**
     * Initializes a new V1 Tender object.
     */
    public function build(): V1Tender
    {
        return CoreHelper::clone($this->instance);
    }
}
