<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ShiftFilter;
use EDD\Vendor\Square\Models\ShiftQuery;
use EDD\Vendor\Square\Models\ShiftSort;

/**
 * Builder for model ShiftQuery
 *
 * @see ShiftQuery
 */
class ShiftQueryBuilder
{
    /**
     * @var ShiftQuery
     */
    private $instance;

    private function __construct(ShiftQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Shift Query Builder object.
     */
    public static function init(): self
    {
        return new self(new ShiftQuery());
    }

    /**
     * Sets filter field.
     *
     * @param ShiftFilter|null $value
     */
    public function filter(?ShiftFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param ShiftSort|null $value
     */
    public function sort(?ShiftSort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Shift Query object.
     */
    public function build(): ShiftQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
