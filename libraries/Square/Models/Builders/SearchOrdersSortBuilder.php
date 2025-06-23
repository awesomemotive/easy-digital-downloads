<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchOrdersSort;

/**
 * Builder for model SearchOrdersSort
 *
 * @see SearchOrdersSort
 */
class SearchOrdersSortBuilder
{
    /**
     * @var SearchOrdersSort
     */
    private $instance;

    private function __construct(SearchOrdersSort $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Orders Sort Builder object.
     *
     * @param string $sortField
     */
    public static function init(string $sortField): self
    {
        return new self(new SearchOrdersSort($sortField));
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
        return $this;
    }

    /**
     * Initializes a new Search Orders Sort object.
     */
    public function build(): SearchOrdersSort
    {
        return CoreHelper::clone($this->instance);
    }
}
