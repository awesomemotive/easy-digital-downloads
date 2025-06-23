<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BookingCustomAttributeUpsertRequest;
use EDD\Vendor\Square\Models\CustomAttribute;

/**
 * Builder for model BookingCustomAttributeUpsertRequest
 *
 * @see BookingCustomAttributeUpsertRequest
 */
class BookingCustomAttributeUpsertRequestBuilder
{
    /**
     * @var BookingCustomAttributeUpsertRequest
     */
    private $instance;

    private function __construct(BookingCustomAttributeUpsertRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Booking Custom Attribute Upsert Request Builder object.
     *
     * @param string $bookingId
     * @param CustomAttribute $customAttribute
     */
    public static function init(string $bookingId, CustomAttribute $customAttribute): self
    {
        return new self(new BookingCustomAttributeUpsertRequest($bookingId, $customAttribute));
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
     * Initializes a new Booking Custom Attribute Upsert Request object.
     */
    public function build(): BookingCustomAttributeUpsertRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
