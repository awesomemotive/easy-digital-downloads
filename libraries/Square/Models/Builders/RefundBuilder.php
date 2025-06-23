<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AdditionalRecipient;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Refund;

/**
 * Builder for model Refund
 *
 * @see Refund
 */
class RefundBuilder
{
    /**
     * @var Refund
     */
    private $instance;

    private function __construct(Refund $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Refund Builder object.
     *
     * @param string $id
     * @param string $locationId
     * @param string $tenderId
     * @param string $reason
     * @param Money $amountMoney
     * @param string $status
     */
    public static function init(
        string $id,
        string $locationId,
        string $tenderId,
        string $reason,
        Money $amountMoney,
        string $status
    ): self {
        return new self(new Refund($id, $locationId, $tenderId, $reason, $amountMoney, $status));
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
     * Initializes a new Refund object.
     */
    public function build(): Refund
    {
        return CoreHelper::clone($this->instance);
    }
}
