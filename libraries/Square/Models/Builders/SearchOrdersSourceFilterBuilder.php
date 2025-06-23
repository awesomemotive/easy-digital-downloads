<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchOrdersSourceFilter;

/**
 * Builder for model SearchOrdersSourceFilter
 *
 * @see SearchOrdersSourceFilter
 */
class SearchOrdersSourceFilterBuilder
{
    /**
     * @var SearchOrdersSourceFilter
     */
    private $instance;

    private function __construct(SearchOrdersSourceFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Orders Source Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchOrdersSourceFilter());
    }

    /**
     * Sets source names field.
     *
     * @param string[]|null $value
     */
    public function sourceNames(?array $value): self
    {
        $this->instance->setSourceNames($value);
        return $this;
    }

    /**
     * Unsets source names field.
     */
    public function unsetSourceNames(): self
    {
        $this->instance->unsetSourceNames();
        return $this;
    }

    /**
     * Initializes a new Search Orders Source Filter object.
     */
    public function build(): SearchOrdersSourceFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
