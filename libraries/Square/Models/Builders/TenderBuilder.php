<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AdditionalRecipient;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Tender;
use EDD\Vendor\Square\Models\TenderBankAccountDetails;
use EDD\Vendor\Square\Models\TenderBuyNowPayLaterDetails;
use EDD\Vendor\Square\Models\TenderCardDetails;
use EDD\Vendor\Square\Models\TenderCashDetails;
use EDD\Vendor\Square\Models\TenderSquareAccountDetails;

/**
 * Builder for model Tender
 *
 * @see Tender
 */
class TenderBuilder
{
    /**
     * @var Tender
     */
    private $instance;

    private function __construct(Tender $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Tender Builder object.
     *
     * @param string $type
     */
    public static function init(string $type): self
    {
        return new self(new Tender($type));
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
     * Sets transaction id field.
     *
     * @param string|null $value
     */
    public function transactionId(?string $value): self
    {
        $this->instance->setTransactionId($value);
        return $this;
    }

    /**
     * Unsets transaction id field.
     */
    public function unsetTransactionId(): self
    {
        $this->instance->unsetTransactionId();
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
     * Unsets note field.
     */
    public function unsetNote(): self
    {
        $this->instance->unsetNote();
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
     * Sets tip money field.
     *
     * @param Money|null $value
     */
    public function tipMoney(?Money $value): self
    {
        $this->instance->setTipMoney($value);
        return $this;
    }

    /**
     * Sets processing fee money field.
     *
     * @param Money|null $value
     */
    public function processingFeeMoney(?Money $value): self
    {
        $this->instance->setProcessingFeeMoney($value);
        return $this;
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
     * Sets card details field.
     *
     * @param TenderCardDetails|null $value
     */
    public function cardDetails(?TenderCardDetails $value): self
    {
        $this->instance->setCardDetails($value);
        return $this;
    }

    /**
     * Sets cash details field.
     *
     * @param TenderCashDetails|null $value
     */
    public function cashDetails(?TenderCashDetails $value): self
    {
        $this->instance->setCashDetails($value);
        return $this;
    }

    /**
     * Sets bank account details field.
     *
     * @param TenderBankAccountDetails|null $value
     */
    public function bankAccountDetails(?TenderBankAccountDetails $value): self
    {
        $this->instance->setBankAccountDetails($value);
        return $this;
    }

    /**
     * Sets buy now pay later details field.
     *
     * @param TenderBuyNowPayLaterDetails|null $value
     */
    public function buyNowPayLaterDetails(?TenderBuyNowPayLaterDetails $value): self
    {
        $this->instance->setBuyNowPayLaterDetails($value);
        return $this;
    }

    /**
     * Sets square account details field.
     *
     * @param TenderSquareAccountDetails|null $value
     */
    public function squareAccountDetails(?TenderSquareAccountDetails $value): self
    {
        $this->instance->setSquareAccountDetails($value);
        return $this;
    }

    /**
     * Sets additional recipients field.
     *
     * @param AdditionalRecipient[]|null $value
     */
    public function additionalRecipients(?array $value): self
    {
        $this->instance->setAdditionalRecipients($value);
        return $this;
    }

    /**
     * Unsets additional recipients field.
     */
    public function unsetAdditionalRecipients(): self
    {
        $this->instance->unsetAdditionalRecipients();
        return $this;
    }

    /**
     * Sets payment id field.
     *
     * @param string|null $value
     */
    public function paymentId(?string $value): self
    {
        $this->instance->setPaymentId($value);
        return $this;
    }

    /**
     * Unsets payment id field.
     */
    public function unsetPaymentId(): self
    {
        $this->instance->unsetPaymentId();
        return $this;
    }

    /**
     * Initializes a new Tender object.
     */
    public function build(): Tender
    {
        return CoreHelper::clone($this->instance);
    }
}
