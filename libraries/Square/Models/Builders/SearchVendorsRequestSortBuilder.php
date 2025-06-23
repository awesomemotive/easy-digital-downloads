<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchVendorsRequestSort;

/**
 * Builder for model SearchVendorsRequestSort
 *
 * @see SearchVendorsRequestSort
 */
class SearchVendorsRequestSortBuilder
{
    /**
     * @var SearchVendorsRequestSort
     */
    private $instance;

    private function __construct(SearchVendorsRequestSort $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Vendors Request Sort Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchVendorsRequestSort());
    }

    /**
     * Sets field field.
     *
     * @param string|null $value
     */
    public function field(?string $value): self
    {
        $this->instance->setField($value);
        return $this;
    }

    /**
     * Sets order field.
     *
     * @param string|null $value
     */
    public function order(?string $value): self
    {
        $this->instance->setOrder($value);
        return $this;
    }

    /**
     * Initializes a new Search Vendors Request Sort object.
     */
    public function build(): SearchVendorsRequestSort
    {
        return CoreHelper::clone($this->instance);
    }
}
