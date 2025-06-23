<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Booking;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveBookingResponse;

/**
 * Builder for model RetrieveBookingResponse
 *
 * @see RetrieveBookingResponse
 */
class RetrieveBookingResponseBuilder
{
    /**
     * @var RetrieveBookingResponse
     */
    private $instance;

    private function __construct(RetrieveBookingResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Booking Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveBookingResponse());
    }

    /**
     * Sets booking field.
     *
     * @param Booking|null $value
     */
    public function booking(?Booking $value): self
    {
        $this->instance->setBooking($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Booking Response object.
     */
    public function build(): RetrieveBookingResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
