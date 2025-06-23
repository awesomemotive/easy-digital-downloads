<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchUpsertCatalogObjectsRequest;
use EDD\Vendor\Square\Models\CatalogObjectBatch;

/**
 * Builder for model BatchUpsertCatalogObjectsRequest
 *
 * @see BatchUpsertCatalogObjectsRequest
 */
class BatchUpsertCatalogObjectsRequestBuilder
{
    /**
     * @var BatchUpsertCatalogObjectsRequest
     */
    private $instance;

    private function __construct(BatchUpsertCatalogObjectsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Upsert Catalog Objects Request Builder object.
     *
     * @param string $idempotencyKey
     * @param CatalogObjectBatch[] $batches
     */
    public static function init(string $idempotencyKey, array $batches): self
    {
        return new self(new BatchUpsertCatalogObjectsRequest($idempotencyKey, $batches));
    }

    /**
     * Initializes a new Batch Upsert Catalog Objects Request object.
     */
    public function build(): BatchUpsertCatalogObjectsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
