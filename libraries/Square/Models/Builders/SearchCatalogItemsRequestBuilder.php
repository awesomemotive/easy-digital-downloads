<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeFilter;
use EDD\Vendor\Square\Models\SearchCatalogItemsRequest;

/**
 * Builder for model SearchCatalogItemsRequest
 *
 * @see SearchCatalogItemsRequest
 */
class SearchCatalogItemsRequestBuilder
{
    /**
     * @var SearchCatalogItemsRequest
     */
    private $instance;

    private function __construct(SearchCatalogItemsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Catalog Items Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchCatalogItemsRequest());
    }

    /**
     * Sets text filter field.
     *
     * @param string|null $value
     */
    public function textFilter(?string $value): self
    {
        $this->instance->setTextFilter($value);
        return $this;
    }

    /**
     * Sets category ids field.
     *
     * @param string[]|null $value
     */
    public function categoryIds(?array $value): self
    {
        $this->instance->setCategoryIds($value);
        return $this;
    }

    /**
     * Sets stock levels field.
     *
     * @param string[]|null $value
     */
    public function stockLevels(?array $value): self
    {
        $this->instance->setStockLevels($value);
        return $this;
    }

    /**
     * Sets enabled location ids field.
     *
     * @param string[]|null $value
     */
    public function enabledLocationIds(?array $value): self
    {
        $this->instance->setEnabledLocationIds($value);
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
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
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
     * Sets product types field.
     *
     * @param string[]|null $value
     */
    public function productTypes(?array $value): self
    {
        $this->instance->setProductTypes($value);
        return $this;
    }

    /**
     * Sets custom attribute filters field.
     *
     * @param CustomAttributeFilter[]|null $value
     */
    public function customAttributeFilters(?array $value): self
    {
        $this->instance->setCustomAttributeFilters($value);
        return $this;
    }

    /**
     * Sets archived state field.
     *
     * @param string|null $value
     */
    public function archivedState(?string $value): self
    {
        $this->instance->setArchivedState($value);
        return $this;
    }

    /**
     * Initializes a new Search Catalog Items Request object.
     */
    public function build(): SearchCatalogItemsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
