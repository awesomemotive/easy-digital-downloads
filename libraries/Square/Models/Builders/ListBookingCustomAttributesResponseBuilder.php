<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListBookingCustomAttributesResponse;

/**
 * Builder for model ListBookingCustomAttributesResponse
 *
 * @see ListBookingCustomAttributesResponse
 */
class ListBookingCustomAttributesResponseBuilder
{
    /**
     * @var ListBookingCustomAttributesResponse
     */
    private $instance;

    private function __construct(ListBookingCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Booking Custom Attributes Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListBookingCustomAttributesResponse());
    }

    /**
     * Sets custom attributes field.
     *
     * @param CustomAttribute[]|null $value
     */
    public function customAttributes(?array $value): self
    {
        $this->instance->setCustomAttributes($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
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
     * Initializes a new List Booking Custom Attributes Response object.
     */
    public function build(): ListBookingCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
