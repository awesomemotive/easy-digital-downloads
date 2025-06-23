<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchVendorsRequest;
use EDD\Vendor\Square\Models\SearchVendorsRequestFilter;
use EDD\Vendor\Square\Models\SearchVendorsRequestSort;

/**
 * Builder for model SearchVendorsRequest
 *
 * @see SearchVendorsRequest
 */
class SearchVendorsRequestBuilder
{
    /**
     * @var SearchVendorsRequest
     */
    private $instance;

    private function __construct(SearchVendorsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Vendors Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchVendorsRequest());
    }

    /**
     * Sets filter field.
     *
     * @param SearchVendorsRequestFilter|null $value
     */
    public function filter(?SearchVendorsRequestFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param SearchVendorsRequestSort|null $value
     */
    public function sort(?SearchVendorsRequestSort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Initializes a new Search Vendors Request object.
     */
    public function build(): SearchVendorsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
