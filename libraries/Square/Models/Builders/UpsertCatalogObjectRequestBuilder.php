<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\UpsertCatalogObjectRequest;

/**
 * Builder for model UpsertCatalogObjectRequest
 *
 * @see UpsertCatalogObjectRequest
 */
class UpsertCatalogObjectRequestBuilder
{
    /**
     * @var UpsertCatalogObjectRequest
     */
    private $instance;

    private function __construct(UpsertCatalogObjectRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Upsert Catalog Object Request Builder object.
     *
     * @param string $idempotencyKey
     * @param CatalogObject $object
     */
    public static function init(string $idempotencyKey, CatalogObject $object): self
    {
        return new self(new UpsertCatalogObjectRequest($idempotencyKey, $object));
    }

    /**
     * Initializes a new Upsert Catalog Object Request object.
     */
    public function build(): UpsertCatalogObjectRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
