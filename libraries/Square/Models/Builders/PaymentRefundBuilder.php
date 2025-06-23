<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DestinationDetails;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\PaymentRefund;
use EDD\Vendor\Square\Models\ProcessingFee;

/**
 * Builder for model PaymentRefund
 *
 * @see PaymentRefund
 */
class PaymentRefundBuilder
{
    /**
     * @var PaymentRefund
     */
    private $instance;

    private function __construct(PaymentRefund $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payment Refund Builder object.
     *
     * @param string $id
     * @param Money $amountMoney
     */
    public static function init(string $id, Money $amountMoney): self
    {
        return new self(new PaymentRefund($id, $amountMoney));
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
     * Unsets status field.
     */
    public function unsetStatus(): self
    {
        $this->instance->unsetStatus();
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
     * Sets unlinked field.
     *
     * @param bool|null $value
     */
    public function unlinked(?bool $value): self
    {
        $this->instance->setUnlinked($value);
        return $this;
    }

    /**
     * Sets destination type field.
     *
     * @param string|null $value
     */
    public function destinationType(?string $value): self
    {
        $this->instance->setDestinationType($value);
        return $this;
    }

    /**
     * Unsets destination type field.
     */
    public function unsetDestinationType(): self
    {
        $this->instance->unsetDestinationType();
        return $this;
    }

    /**
     * Sets destination details field.
     *
     * @param DestinationDetails|null $value
     */
    public function destinationDetails(?DestinationDetails $value): self
    {
        $this->instance->setDestinationDetails($value);
        return $this;
    }

    /**
     * Sets app fee money field.
     *
     * @param Money|null $value
     */
    public function appFeeMoney(?Money $value): self
    {
        $this->instance->setAppFeeMoney($value);
        return $this;
    }

    /**
     * Sets processing fee field.
     *
     * @param ProcessingFee[]|null $value
     */
    public function processingFee(?array $value): self
    {
        $this->instance->setProcessingFee($value);
        return $this;
    }

    /**
     * Unsets processing fee field.
     */
    public function unsetProcessingFee(): self
    {
        $this->instance->unsetProcessingFee();
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
     * Unsets reason field.
     */
    public function unsetReason(): self
    {
        $this->instance->unsetReason();
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
     * Sets team member id field.
     *
     * @param string|null $value
     */
    public function teamMemberId(?string $value): self
    {
        $this->instance->setTeamMemberId($value);
        return $this;
    }

    /**
     * Sets terminal refund id field.
     *
     * @param string|null $value
     */
    public function terminalRefundId(?string $value): self
    {
        $this->instance->setTerminalRefundId($value);
        return $this;
    }

    /**
     * Initializes a new Payment Refund object.
     */
    public function build(): PaymentRefund
    {
        return CoreHelper::clone($this->instance);
    }
}
