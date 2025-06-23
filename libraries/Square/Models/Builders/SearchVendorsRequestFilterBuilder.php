<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchVendorsRequestFilter;

/**
 * Builder for model SearchVendorsRequestFilter
 *
 * @see SearchVendorsRequestFilter
 */
class SearchVendorsRequestFilterBuilder
{
    /**
     * @var SearchVendorsRequestFilter
     */
    private $instance;

    private function __construct(SearchVendorsRequestFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Vendors Request Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchVendorsRequestFilter());
    }

    /**
     * Sets name field.
     *
     * @param string[]|null $value
     */
    public function name(?array $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string[]|null $value
     */
    public function status(?array $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Unsets status field.
     */
    public function unsetStatus(): self
    {
        $this->instance->unsetStatus();
        return $this;
    }

    /**
     * Initializes a new Search Vendors Request Filter object.
     */
    public function build(): SearchVendorsRequestFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
