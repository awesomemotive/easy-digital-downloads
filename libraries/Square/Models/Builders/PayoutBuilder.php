<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Destination;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Payout;
use EDD\Vendor\Square\Models\PayoutFee;

/**
 * Builder for model Payout
 *
 * @see Payout
 */
class PayoutBuilder
{
    /**
     * @var Payout
     */
    private $instance;

    private function __construct(Payout $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Payout Builder object.
     *
     * @param string $id
     * @param string $locationId
     */
    public static function init(string $id, string $locationId): self
    {
        return new self(new Payout($id, $locationId));
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
     * Sets destination field.
     *
     * @param Destination|null $value
     */
    public function destination(?Destination $value): self
    {
        $this->instance->setDestination($value);
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
     * Sets payout fee field.
     *
     * @param PayoutFee[]|null $value
     */
    public function payoutFee(?array $value): self
    {
        $this->instance->setPayoutFee($value);
        return $this;
    }

    /**
     * Unsets payout fee field.
     */
    public function unsetPayoutFee(): self
    {
        $this->instance->unsetPayoutFee();
        return $this;
    }

    /**
     * Sets arrival date field.
     *
     * @param string|null $value
     */
    public function arrivalDate(?string $value): self
    {
        $this->instance->setArrivalDate($value);
        return $this;
    }

    /**
     * Unsets arrival date field.
     */
    public function unsetArrivalDate(): self
    {
        $this->instance->unsetArrivalDate();
        return $this;
    }

    /**
     * Sets end to end id field.
     *
     * @param string|null $value
     */
    public function endToEndId(?string $value): self
    {
        $this->instance->setEndToEndId($value);
        return $this;
    }

    /**
     * Unsets end to end id field.
     */
    public function unsetEndToEndId(): self
    {
        $this->instance->unsetEndToEndId();
        return $this;
    }

    /**
     * Initializes a new Payout object.
     */
    public function build(): Payout
    {
        return CoreHelper::clone($this->instance);
    }
}
