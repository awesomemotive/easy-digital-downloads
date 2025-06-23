<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQuery;
use EDD\Vendor\Square\Models\SearchCatalogObjectsRequest;

/**
 * Builder for model SearchCatalogObjectsRequest
 *
 * @see SearchCatalogObjectsRequest
 */
class SearchCatalogObjectsRequestBuilder
{
    /**
     * @var SearchCatalogObjectsRequest
     */
    private $instance;

    private function __construct(SearchCatalogObjectsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Catalog Objects Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchCatalogObjectsRequest());
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
     * Sets object types field.
     *
     * @param string[]|null $value
     */
    public function objectTypes(?array $value): self
    {
        $this->instance->setObjectTypes($value);
        return $this;
    }

    /**
     * Sets include deleted objects field.
     *
     * @param bool|null $value
     */
    public function includeDeletedObjects(?bool $value): self
    {
        $this->instance->setIncludeDeletedObjects($value);
        return $this;
    }

    /**
     * Sets include related objects field.
     *
     * @param bool|null $value
     */
    public function includeRelatedObjects(?bool $value): self
    {
        $this->instance->setIncludeRelatedObjects($value);
        return $this;
    }

    /**
     * Sets begin time field.
     *
     * @param string|null $value
     */
    public function beginTime(?string $value): self
    {
        $this->instance->setBeginTime($value);
        return $this;
    }

    /**
     * Sets query field.
     *
     * @param CatalogQuery|null $value
     */
    public function query(?CatalogQuery $value): self
    {
        $this->instance->setQuery($value);
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
     * Sets include category path to root field.
     *
     * @param bool|null $value
     */
    public function includeCategoryPathToRoot(?bool $value): self
    {
        $this->instance->setIncludeCategoryPathToRoot($value);
        return $this;
    }

    /**
     * Initializes a new Search Catalog Objects Request object.
     */
    public function build(): SearchCatalogObjectsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
