<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Booking;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpdateBookingResponse;

/**
 * Builder for model UpdateBookingResponse
 *
 * @see UpdateBookingResponse
 */
class UpdateBookingResponseBuilder
{
    /**
     * @var UpdateBookingResponse
     */
    private $instance;

    private function __construct(UpdateBookingResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Booking Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateBookingResponse());
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
     * Initializes a new Update Booking Response object.
     */
    public function build(): UpdateBookingResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
