<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchOrdersFilter;
use EDD\Vendor\Square\Models\SearchOrdersQuery;
use EDD\Vendor\Square\Models\SearchOrdersSort;

/**
 * Builder for model SearchOrdersQuery
 *
 * @see SearchOrdersQuery
 */
class SearchOrdersQueryBuilder
{
    /**
     * @var SearchOrdersQuery
     */
    private $instance;

    private function __construct(SearchOrdersQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Orders Query Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchOrdersQuery());
    }

    /**
     * Sets filter field.
     *
     * @param SearchOrdersFilter|null $value
     */
    public function filter(?SearchOrdersFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param SearchOrdersSort|null $value
     */
    public function sort(?SearchOrdersSort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Search Orders Query object.
     */
    public function build(): SearchOrdersQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
