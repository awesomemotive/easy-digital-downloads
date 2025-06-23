<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Address;
use EDD\Vendor\Square\Models\AppointmentSegment;
use EDD\Vendor\Square\Models\Booking;
use EDD\Vendor\Square\Models\BookingCreatorDetails;

/**
 * Builder for model Booking
 *
 * @see Booking
 */
class BookingBuilder
{
    /**
     * @var Booking
     */
    private $instance;

    private function __construct(Booking $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Booking Builder object.
     */
    public static function init(): self
    {
        return new self(new Booking());
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
     * Sets start at field.
     *
     * @param string|null $value
     */
    public function startAt(?string $value): self
    {
        $this->instance->setStartAt($value);
        return $this;
    }

    /**
     * Unsets start at field.
     */
    public function unsetStartAt(): self
    {
        $this->instance->unsetStartAt();
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
     * Sets customer note field.
     *
     * @param string|null $value
     */
    public function customerNote(?string $value): self
    {
        $this->instance->setCustomerNote($value);
        return $this;
    }

    /**
     * Unsets customer note field.
     */
    public function unsetCustomerNote(): self
    {
        $this->instance->unsetCustomerNote();
        return $this;
    }

    /**
     * Sets seller note field.
     *
     * @param string|null $value
     */
    public function sellerNote(?string $value): self
    {
        $this->instance->setSellerNote($value);
        return $this;
    }

    /**
     * Unsets seller note field.
     */
    public function unsetSellerNote(): self
    {
        $this->instance->unsetSellerNote();
        return $this;
    }

    /**
     * Sets appointment segments field.
     *
     * @param AppointmentSegment[]|null $value
     */
    public function appointmentSegments(?array $value): self
    {
        $this->instance->setAppointmentSegments($value);
        return $this;
    }

    /**
     * Unsets appointment segments field.
     */
    public function unsetAppointmentSegments(): self
    {
        $this->instance->unsetAppointmentSegments();
        return $this;
    }

    /**
     * Sets transition time minutes field.
     *
     * @param int|null $value
     */
    public function transitionTimeMinutes(?int $value): self
    {
        $this->instance->setTransitionTimeMinutes($value);
        return $this;
    }

    /**
     * Sets all day field.
     *
     * @param bool|null $value
     */
    public function allDay(?bool $value): self
    {
        $this->instance->setAllDay($value);
        return $this;
    }

    /**
     * Sets location type field.
     *
     * @param string|null $value
     */
    public function locationType(?string $value): self
    {
        $this->instance->setLocationType($value);
        return $this;
    }

    /**
     * Sets creator details field.
     *
     * @param BookingCreatorDetails|null $value
     */
    public function creatorDetails(?BookingCreatorDetails $value): self
    {
        $this->instance->setCreatorDetails($value);
        return $this;
    }

    /**
     * Sets source field.
     *
     * @param string|null $value
     */
    public function source(?string $value): self
    {
        $this->instance->setSource($value);
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
     * Initializes a new Booking object.
     */
    public function build(): Booking
    {
        return CoreHelper::clone($this->instance);
    }
}
