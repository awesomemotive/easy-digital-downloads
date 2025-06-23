<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchRetrieveCatalogObjectsRequest;

/**
 * Builder for model BatchRetrieveCatalogObjectsRequest
 *
 * @see BatchRetrieveCatalogObjectsRequest
 */
class BatchRetrieveCatalogObjectsRequestBuilder
{
    /**
     * @var BatchRetrieveCatalogObjectsRequest
     */
    private $instance;

    private function __construct(BatchRetrieveCatalogObjectsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Retrieve Catalog Objects Request Builder object.
     *
     * @param string[] $objectIds
     */
    public static function init(array $objectIds): self
    {
        return new self(new BatchRetrieveCatalogObjectsRequest($objectIds));
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
     * Unsets include related objects field.
     */
    public function unsetIncludeRelatedObjects(): self
    {
        $this->instance->unsetIncludeRelatedObjects();
        return $this;
    }

    /**
     * Sets catalog version field.
     *
     * @param int|null $value
     */
    public function catalogVersion(?int $value): self
    {
        $this->instance->setCatalogVersion($value);
        return $this;
    }

    /**
     * Unsets catalog version field.
     */
    public function unsetCatalogVersion(): self
    {
        $this->instance->unsetCatalogVersion();
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
     * Unsets include deleted objects field.
     */
    public function unsetIncludeDeletedObjects(): self
    {
        $this->instance->unsetIncludeDeletedObjects();
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
     * Unsets include category path to root field.
     */
    public function unsetIncludeCategoryPathToRoot(): self
    {
        $this->instance->unsetIncludeCategoryPathToRoot();
        return $this;
    }

    /**
     * Initializes a new Batch Retrieve Catalog Objects Request object.
     */
    public function build(): BatchRetrieveCatalogObjectsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
