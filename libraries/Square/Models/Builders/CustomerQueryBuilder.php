<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerFilter;
use EDD\Vendor\Square\Models\CustomerQuery;
use EDD\Vendor\Square\Models\CustomerSort;

/**
 * Builder for model CustomerQuery
 *
 * @see CustomerQuery
 */
class CustomerQueryBuilder
{
    /**
     * @var CustomerQuery
     */
    private $instance;

    private function __construct(CustomerQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Query Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomerQuery());
    }

    /**
     * Sets filter field.
     *
     * @param CustomerFilter|null $value
     */
    public function filter(?CustomerFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param CustomerSort|null $value
     */
    public function sort(?CustomerSort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Customer Query object.
     */
    public function build(): CustomerQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
