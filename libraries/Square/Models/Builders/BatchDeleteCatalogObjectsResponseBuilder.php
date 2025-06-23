<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchDeleteCatalogObjectsResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BatchDeleteCatalogObjectsResponse
 *
 * @see BatchDeleteCatalogObjectsResponse
 */
class BatchDeleteCatalogObjectsResponseBuilder
{
    /**
     * @var BatchDeleteCatalogObjectsResponse
     */
    private $instance;

    private function __construct(BatchDeleteCatalogObjectsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Delete Catalog Objects Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchDeleteCatalogObjectsResponse());
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
     * Sets deleted object ids field.
     *
     * @param string[]|null $value
     */
    public function deletedObjectIds(?array $value): self
    {
        $this->instance->setDeletedObjectIds($value);
        return $this;
    }

    /**
     * Sets deleted at field.
     *
     * @param string|null $value
     */
    public function deletedAt(?string $value): self
    {
        $this->instance->setDeletedAt($value);
        return $this;
    }

    /**
     * Initializes a new Batch Delete Catalog Objects Response object.
     */
    public function build(): BatchDeleteCatalogObjectsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
