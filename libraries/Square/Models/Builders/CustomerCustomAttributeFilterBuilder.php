<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerCustomAttributeFilter;
use EDD\Vendor\Square\Models\CustomerCustomAttributeFilterValue;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model CustomerCustomAttributeFilter
 *
 * @see CustomerCustomAttributeFilter
 */
class CustomerCustomAttributeFilterBuilder
{
    /**
     * @var CustomerCustomAttributeFilter
     */
    private $instance;

    private function __construct(CustomerCustomAttributeFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Custom Attribute Filter Builder object.
     *
     * @param string $key
     */
    public static function init(string $key): self
    {
        return new self(new CustomerCustomAttributeFilter($key));
    }

    /**
     * Sets filter field.
     *
     * @param CustomerCustomAttributeFilterValue|null $value
     */
    public function filter(?CustomerCustomAttributeFilterValue $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param TimeRange|null $value
     */
    public function updatedAt(?TimeRange $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Customer Custom Attribute Filter object.
     */
    public function build(): CustomerCustomAttributeFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
