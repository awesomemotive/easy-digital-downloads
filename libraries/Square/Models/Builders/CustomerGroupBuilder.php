<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerGroup;

/**
 * Builder for model CustomerGroup
 *
 * @see CustomerGroup
 */
class CustomerGroupBuilder
{
    /**
     * @var CustomerGroup
     */
    private $instance;

    private function __construct(CustomerGroup $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Group Builder object.
     *
     * @param string $name
     */
    public static function init(string $name): self
    {
        return new self(new CustomerGroup($name));
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
     * Initializes a new Customer Group object.
     */
    public function build(): CustomerGroup
    {
        return CoreHelper::clone($this->instance);
    }
}
