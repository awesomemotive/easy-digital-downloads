<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;
use EDD\Vendor\Square\Models\UpdateBookingCustomAttributeDefinitionRequest;

/**
 * Builder for model UpdateBookingCustomAttributeDefinitionRequest
 *
 * @see UpdateBookingCustomAttributeDefinitionRequest
 */
class UpdateBookingCustomAttributeDefinitionRequestBuilder
{
    /**
     * @var UpdateBookingCustomAttributeDefinitionRequest
     */
    private $instance;

    private function __construct(UpdateBookingCustomAttributeDefinitionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Booking Custom Attribute Definition Request Builder object.
     *
     * @param CustomAttributeDefinition $customAttributeDefinition
     */
    public static function init(CustomAttributeDefinition $customAttributeDefinition): self
    {
        return new self(new UpdateBookingCustomAttributeDefinitionRequest($customAttributeDefinition));
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
     * Initializes a new Update Booking Custom Attribute Definition Request object.
     */
    public function build(): UpdateBookingCustomAttributeDefinitionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
