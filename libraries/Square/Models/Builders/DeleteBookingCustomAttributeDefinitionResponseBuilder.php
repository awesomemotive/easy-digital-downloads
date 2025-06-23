<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteBookingCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteBookingCustomAttributeDefinitionResponse
 *
 * @see DeleteBookingCustomAttributeDefinitionResponse
 */
class DeleteBookingCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var DeleteBookingCustomAttributeDefinitionResponse
     */
    private $instance;

    private function __construct(DeleteBookingCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Booking Custom Attribute Definition Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteBookingCustomAttributeDefinitionResponse());
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
     * Initializes a new Delete Booking Custom Attribute Definition Response object.
     */
    public function build(): DeleteBookingCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
