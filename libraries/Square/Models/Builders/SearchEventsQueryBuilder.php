<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchEventsFilter;
use EDD\Vendor\Square\Models\SearchEventsQuery;
use EDD\Vendor\Square\Models\SearchEventsSort;

/**
 * Builder for model SearchEventsQuery
 *
 * @see SearchEventsQuery
 */
class SearchEventsQueryBuilder
{
    /**
     * @var SearchEventsQuery
     */
    private $instance;

    private function __construct(SearchEventsQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Events Query Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchEventsQuery());
    }

    /**
     * Sets filter field.
     *
     * @param SearchEventsFilter|null $value
     */
    public function filter(?SearchEventsFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param SearchEventsSort|null $value
     */
    public function sort(?SearchEventsSort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Search Events Query object.
     */
    public function build(): SearchEventsQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
