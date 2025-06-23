<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchOrdersDateTimeFilter;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model SearchOrdersDateTimeFilter
 *
 * @see SearchOrdersDateTimeFilter
 */
class SearchOrdersDateTimeFilterBuilder
{
    /**
     * @var SearchOrdersDateTimeFilter
     */
    private $instance;

    private function __construct(SearchOrdersDateTimeFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Orders Date Time Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchOrdersDateTimeFilter());
    }

    /**
     * Sets created at field.
     *
     * @param TimeRange|null $value
     */
    public function createdAt(?TimeRange $value): self
    {
        $this->instance->setCreatedAt($value);
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
     * Sets closed at field.
     *
     * @param TimeRange|null $value
     */
    public function closedAt(?TimeRange $value): self
    {
        $this->instance->setClosedAt($value);
        return $this;
    }

    /**
     * Initializes a new Search Orders Date Time Filter object.
     */
    public function build(): SearchOrdersDateTimeFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
