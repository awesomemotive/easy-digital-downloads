<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BookingCustomAttributeUpsertResponse;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BookingCustomAttributeUpsertResponse
 *
 * @see BookingCustomAttributeUpsertResponse
 */
class BookingCustomAttributeUpsertResponseBuilder
{
    /**
     * @var BookingCustomAttributeUpsertResponse
     */
    private $instance;

    private function __construct(BookingCustomAttributeUpsertResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Booking Custom Attribute Upsert Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BookingCustomAttributeUpsertResponse());
    }

    /**
     * Sets booking id field.
     *
     * @param string|null $value
     */
    public function bookingId(?string $value): self
    {
        $this->instance->setBookingId($value);
        return $this;
    }

    /**
     * Sets custom attribute field.
     *
     * @param CustomAttribute|null $value
     */
    public function customAttribute(?CustomAttribute $value): self
    {
        $this->instance->setCustomAttribute($value);
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
     * Initializes a new Booking Custom Attribute Upsert Response object.
     */
    public function build(): BookingCustomAttributeUpsertResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
