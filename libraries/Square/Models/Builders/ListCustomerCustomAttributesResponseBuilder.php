<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListCustomerCustomAttributesResponse;

/**
 * Builder for model ListCustomerCustomAttributesResponse
 *
 * @see ListCustomerCustomAttributesResponse
 */
class ListCustomerCustomAttributesResponseBuilder
{
    /**
     * @var ListCustomerCustomAttributesResponse
     */
    private $instance;

    private function __construct(ListCustomerCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Customer Custom Attributes Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCustomerCustomAttributesResponse());
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
     * Initializes a new List Customer Custom Attributes Response object.
     */
    public function build(): ListCustomerCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
