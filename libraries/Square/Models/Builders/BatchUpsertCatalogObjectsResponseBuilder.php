<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchUpsertCatalogObjectsResponse;
use EDD\Vendor\Square\Models\CatalogIdMapping;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BatchUpsertCatalogObjectsResponse
 *
 * @see BatchUpsertCatalogObjectsResponse
 */
class BatchUpsertCatalogObjectsResponseBuilder
{
    /**
     * @var BatchUpsertCatalogObjectsResponse
     */
    private $instance;

    private function __construct(BatchUpsertCatalogObjectsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Upsert Catalog Objects Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchUpsertCatalogObjectsResponse());
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
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets id mappings field.
     *
     * @param CatalogIdMapping[]|null $value
     */
    public function idMappings(?array $value): self
    {
        $this->instance->setIdMappings($value);
        return $this;
    }

    /**
     * Initializes a new Batch Upsert Catalog Objects Response object.
     */
    public function build(): BatchUpsertCatalogObjectsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
