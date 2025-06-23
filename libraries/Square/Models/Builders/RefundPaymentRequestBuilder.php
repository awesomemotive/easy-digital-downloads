<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DestinationDetailsCashRefundDetails;
use EDD\Vendor\Square\Models\DestinationDetailsExternalRefundDetails;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\RefundPaymentRequest;

/**
 * Builder for model RefundPaymentRequest
 *
 * @see RefundPaymentRequest
 */
class RefundPaymentRequestBuilder
{
    /**
     * @var RefundPaymentRequest
     */
    private $instance;

    private function __construct(RefundPaymentRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Refund Payment Request Builder object.
     *
     * @param string $idempotencyKey
     * @param Money $amountMoney
     */
    public static function init(string $idempotencyKey, Money $amountMoney): self
    {
        return new self(new RefundPaymentRequest($idempotencyKey, $amountMoney));
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
     * Sets destination id field.
     *
     * @param string|null $value
     */
    public function destinationId(?string $value): self
    {
        $this->instance->setDestinationId($value);
        return $this;
    }

    /**
     * Unsets destination id field.
     */
    public function unsetDestinationId(): self
    {
        $this->instance->unsetDestinationId();
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
     * Unsets unlinked field.
     */
    public function unsetUnlinked(): self
    {
        $this->instance->unsetUnlinked();
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
     * Sets payment version token field.
     *
     * @param string|null $value
     */
    public function paymentVersionToken(?string $value): self
    {
        $this->instance->setPaymentVersionToken($value);
        return $this;
    }

    /**
     * Unsets payment version token field.
     */
    public function unsetPaymentVersionToken(): self
    {
        $this->instance->unsetPaymentVersionToken();
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
     * Unsets team member id field.
     */
    public function unsetTeamMemberId(): self
    {
        $this->instance->unsetTeamMemberId();
        return $this;
    }

    /**
     * Sets cash details field.
     *
     * @param DestinationDetailsCashRefundDetails|null $value
     */
    public function cashDetails(?DestinationDetailsCashRefundDetails $value): self
    {
        $this->instance->setCashDetails($value);
        return $this;
    }

    /**
     * Sets external details field.
     *
     * @param DestinationDetailsExternalRefundDetails|null $value
     */
    public function externalDetails(?DestinationDetailsExternalRefundDetails $value): self
    {
        $this->instance->setExternalDetails($value);
        return $this;
    }

    /**
     * Initializes a new Refund Payment Request object.
     */
    public function build(): RefundPaymentRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
