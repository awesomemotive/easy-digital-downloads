<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchCatalogObjectsResponse;

/**
 * Builder for model SearchCatalogObjectsResponse
 *
 * @see SearchCatalogObjectsResponse
 */
class SearchCatalogObjectsResponseBuilder
{
    /**
     * @var SearchCatalogObjectsResponse
     */
    private $instance;

    private function __construct(SearchCatalogObjectsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Catalog Objects Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchCatalogObjectsResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
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
     * Sets objects field.
     *
     * @param CatalogObject[]|null $value
     */
    public function objects(?array $value): self
    {
        $this->instance->setObjects($value);
        return $this;
    }

    /**
     * Sets related objects field.
     *
     * @param CatalogObject[]|null $value
     */
    public function relatedObjects(?array $value): self
    {
        $this->instance->setRelatedObjects($value);
        return $this;
    }

    /**
     * Sets latest time field.
     *
     * @param string|null $value
     */
    public function latestTime(?string $value): self
    {
        $this->instance->setLatestTime($value);
        return $this;
    }

    /**
     * Initializes a new Search Catalog Objects Response object.
     */
    public function build(): SearchCatalogObjectsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
