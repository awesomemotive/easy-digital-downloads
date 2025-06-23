<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerSegment;

/**
 * Builder for model CustomerSegment
 *
 * @see CustomerSegment
 */
class CustomerSegmentBuilder
{
    /**
     * @var CustomerSegment
     */
    private $instance;

    private function __construct(CustomerSegment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Segment Builder object.
     *
     * @param string $name
     */
    public static function init(string $name): self
    {
        return new self(new CustomerSegment($name));
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Customer Segment object.
     */
    public function build(): CustomerSegment
    {
        return CoreHelper::clone($this->instance);
    }
}
