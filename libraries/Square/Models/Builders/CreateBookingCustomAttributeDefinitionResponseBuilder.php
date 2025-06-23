<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateBookingCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model CreateBookingCustomAttributeDefinitionResponse
 *
 * @see CreateBookingCustomAttributeDefinitionResponse
 */
class CreateBookingCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var CreateBookingCustomAttributeDefinitionResponse
     */
    private $instance;

    private function __construct(CreateBookingCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Booking Custom Attribute Definition Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateBookingCustomAttributeDefinitionResponse());
    }

    /**
     * Sets custom attribute definition field.
     *
     * @param CustomAttributeDefinition|null $value
     */
    public function customAttributeDefinition(?CustomAttributeDefinition $value): self
    {
        $this->instance->setCustomAttributeDefinition($value);
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
     * Initializes a new Create Booking Custom Attribute Definition Response object.
     */
    public function build(): CreateBookingCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
