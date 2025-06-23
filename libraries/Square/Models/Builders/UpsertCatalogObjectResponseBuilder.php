<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogIdMapping;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpsertCatalogObjectResponse;

/**
 * Builder for model UpsertCatalogObjectResponse
 *
 * @see UpsertCatalogObjectResponse
 */
class UpsertCatalogObjectResponseBuilder
{
    /**
     * @var UpsertCatalogObjectResponse
     */
    private $instance;

    private function __construct(UpsertCatalogObjectResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Upsert Catalog Object Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpsertCatalogObjectResponse());
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
     * Sets catalog object field.
     *
     * @param CatalogObject|null $value
     */
    public function catalogObject(?CatalogObject $value): self
    {
        $this->instance->setCatalogObject($value);
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
     * Initializes a new Upsert Catalog Object Response object.
     */
    public function build(): UpsertCatalogObjectResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
