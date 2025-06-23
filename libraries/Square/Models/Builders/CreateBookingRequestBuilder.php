<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Booking;
use EDD\Vendor\Square\Models\CreateBookingRequest;

/**
 * Builder for model CreateBookingRequest
 *
 * @see CreateBookingRequest
 */
class CreateBookingRequestBuilder
{
    /**
     * @var CreateBookingRequest
     */
    private $instance;

    private function __construct(CreateBookingRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Booking Request Builder object.
     *
     * @param Booking $booking
     */
    public static function init(Booking $booking): self
    {
        return new self(new CreateBookingRequest($booking));
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }

    /**
     * Initializes a new Create Booking Request object.
     */
    public function build(): CreateBookingRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
