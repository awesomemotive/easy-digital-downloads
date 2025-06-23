<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\CatalogObjectBatch;

/**
 * Builder for model CatalogObjectBatch
 *
 * @see CatalogObjectBatch
 */
class CatalogObjectBatchBuilder
{
    /**
     * @var CatalogObjectBatch
     */
    private $instance;

    private function __construct(CatalogObjectBatch $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Object Batch Builder object.
     *
     * @param CatalogObject[] $objects
     */
    public static function init(array $objects): self
    {
        return new self(new CatalogObjectBatch($objects));
    }

    /**
     * Initializes a new Catalog Object Batch object.
     */
    public function build(): CatalogObjectBatch
    {
        return CoreHelper::clone($this->instance);
    }
}
