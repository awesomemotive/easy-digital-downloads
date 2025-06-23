<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchCatalogItemsResponse;

/**
 * Builder for model SearchCatalogItemsResponse
 *
 * @see SearchCatalogItemsResponse
 */
class SearchCatalogItemsResponseBuilder
{
    /**
     * @var SearchCatalogItemsResponse
     */
    private $instance;

    private function __construct(SearchCatalogItemsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Catalog Items Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchCatalogItemsResponse());
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
     * Sets items field.
     *
     * @param CatalogObject[]|null $value
     */
    public function items(?array $value): self
    {
        $this->instance->setItems($value);
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
     * Sets matched variation ids field.
     *
     * @param string[]|null $value
     */
    public function matchedVariationIds(?array $value): self
    {
        $this->instance->setMatchedVariationIds($value);
        return $this;
    }

    /**
     * Initializes a new Search Catalog Items Response object.
     */
    public function build(): SearchCatalogItemsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
