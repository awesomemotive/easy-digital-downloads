<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelBookingRequest;

/**
 * Builder for model CancelBookingRequest
 *
 * @see CancelBookingRequest
 */
class CancelBookingRequestBuilder
{
    /**
     * @var CancelBookingRequest
     */
    private $instance;

    private function __construct(CancelBookingRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Booking Request Builder object.
     */
    public static function init(): self
    {
        return new self(new CancelBookingRequest());
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
     * Unsets idempotency key field.
     */
    public function unsetIdempotencyKey(): self
    {
        $this->instance->unsetIdempotencyKey();
        return $this;
    }

    /**
     * Sets booking version field.
     *
     * @param int|null $value
     */
    public function bookingVersion(?int $value): self
    {
        $this->instance->setBookingVersion($value);
        return $this;
    }

    /**
     * Unsets booking version field.
     */
    public function unsetBookingVersion(): self
    {
        $this->instance->unsetBookingVersion();
        return $this;
    }

    /**
     * Initializes a new Cancel Booking Request object.
     */
    public function build(): CancelBookingRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
