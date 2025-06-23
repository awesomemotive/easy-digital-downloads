<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchAvailabilityFilter;
use EDD\Vendor\Square\Models\SearchAvailabilityQuery;

/**
 * Builder for model SearchAvailabilityQuery
 *
 * @see SearchAvailabilityQuery
 */
class SearchAvailabilityQueryBuilder
{
    /**
     * @var SearchAvailabilityQuery
     */
    private $instance;

    private function __construct(SearchAvailabilityQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Availability Query Builder object.
     *
     * @param SearchAvailabilityFilter $filter
     */
    public static function init(SearchAvailabilityFilter $filter): self
    {
        return new self(new SearchAvailabilityQuery($filter));
    }

    /**
     * Initializes a new Search Availability Query object.
     */
    public function build(): SearchAvailabilityQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
