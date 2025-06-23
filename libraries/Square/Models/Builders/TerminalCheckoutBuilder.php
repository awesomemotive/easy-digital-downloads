<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceCheckoutOptions;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\PaymentOptions;
use EDD\Vendor\Square\Models\TerminalCheckout;

/**
 * Builder for model TerminalCheckout
 *
 * @see TerminalCheckout
 */
class TerminalCheckoutBuilder
{
    /**
     * @var TerminalCheckout
     */
    private $instance;

    private function __construct(TerminalCheckout $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Checkout Builder object.
     *
     * @param Money $amountMoney
     * @param DeviceCheckoutOptions $deviceOptions
     */
    public static function init(Money $amountMoney, DeviceCheckoutOptions $deviceOptions): self
    {
        return new self(new TerminalCheckout($amountMoney, $deviceOptions));
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
     * Unsets reference id field.
     */
    public function unsetReferenceId(): self
    {
        $this->instance->unsetReferenceId();
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
     * Sets payment options field.
     *
     * @param PaymentOptions|null $value
     */
    public function paymentOptions(?PaymentOptions $value): self
    {
        $this->instance->setPaymentOptions($value);
        return $this;
    }

    /**
     * Sets deadline duration field.
     *
     * @param string|null $value
     */
    public function deadlineDuration(?string $value): self
    {
        $this->instance->setDeadlineDuration($value);
        return $this;
    }

    /**
     * Unsets deadline duration field.
     */
    public function unsetDeadlineDuration(): self
    {
        $this->instance->unsetDeadlineDuration();
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
     * Sets cancel reason field.
     *
     * @param string|null $value
     */
    public function cancelReason(?string $value): self
    {
        $this->instance->setCancelReason($value);
        return $this;
    }

    /**
     * Sets payment ids field.
     *
     * @param string[]|null $value
     */
    public function paymentIds(?array $value): self
    {
        $this->instance->setPaymentIds($value);
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
     * Sets app id field.
     *
     * @param string|null $value
     */
    public function appId(?string $value): self
    {
        $this->instance->setAppId($value);
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
     * Sets payment type field.
     *
     * @param string|null $value
     */
    public function paymentType(?string $value): self
    {
        $this->instance->setPaymentType($value);
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
     * Sets statement description identifier field.
     *
     * @param string|null $value
     */
    public function statementDescriptionIdentifier(?string $value): self
    {
        $this->instance->setStatementDescriptionIdentifier($value);
        return $this;
    }

    /**
     * Unsets statement description identifier field.
     */
    public function unsetStatementDescriptionIdentifier(): self
    {
        $this->instance->unsetStatementDescriptionIdentifier();
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
     * Initializes a new Terminal Checkout object.
     */
    public function build(): TerminalCheckout
    {
        return CoreHelper::clone($this->instance);
    }
}
