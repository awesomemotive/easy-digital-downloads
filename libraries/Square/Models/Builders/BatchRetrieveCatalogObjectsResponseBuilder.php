<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchRetrieveCatalogObjectsResponse;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BatchRetrieveCatalogObjectsResponse
 *
 * @see BatchRetrieveCatalogObjectsResponse
 */
class BatchRetrieveCatalogObjectsResponseBuilder
{
    /**
     * @var BatchRetrieveCatalogObjectsResponse
     */
    private $instance;

    private function __construct(BatchRetrieveCatalogObjectsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Retrieve Catalog Objects Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchRetrieveCatalogObjectsResponse());
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
     * Initializes a new Batch Retrieve Catalog Objects Response object.
     */
    public function build(): BatchRetrieveCatalogObjectsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
