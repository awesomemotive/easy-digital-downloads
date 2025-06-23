<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchDeleteCatalogObjectsRequest;

/**
 * Builder for model BatchDeleteCatalogObjectsRequest
 *
 * @see BatchDeleteCatalogObjectsRequest
 */
class BatchDeleteCatalogObjectsRequestBuilder
{
    /**
     * @var BatchDeleteCatalogObjectsRequest
     */
    private $instance;

    private function __construct(BatchDeleteCatalogObjectsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Delete Catalog Objects Request Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchDeleteCatalogObjectsRequest());
    }

    /**
     * Sets object ids field.
     *
     * @param string[]|null $value
     */
    public function objectIds(?array $value): self
    {
        $this->instance->setObjectIds($value);
        return $this;
    }

    /**
     * Unsets object ids field.
     */
    public function unsetObjectIds(): self
    {
        $this->instance->unsetObjectIds();
        return $this;
    }

    /**
     * Initializes a new Batch Delete Catalog Objects Request object.
     */
    public function build(): BatchDeleteCatalogObjectsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
