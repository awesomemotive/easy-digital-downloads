<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AppointmentSegment;
use EDD\Vendor\Square\Models\Availability;

/**
 * Builder for model Availability
 *
 * @see Availability
 */
class AvailabilityBuilder
{
    /**
     * @var Availability
     */
    private $instance;

    private function __construct(Availability $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Availability Builder object.
     */
    public static function init(): self
    {
        return new self(new Availability());
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
     * Initializes a new Availability object.
     */
    public function build(): Availability
    {
        return CoreHelper::clone($this->instance);
    }
}
