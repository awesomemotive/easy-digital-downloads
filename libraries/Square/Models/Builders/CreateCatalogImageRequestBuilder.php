<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObject;
use EDD\Vendor\Square\Models\CreateCatalogImageRequest;

/**
 * Builder for model CreateCatalogImageRequest
 *
 * @see CreateCatalogImageRequest
 */
class CreateCatalogImageRequestBuilder
{
    /**
     * @var CreateCatalogImageRequest
     */
    private $instance;

    private function __construct(CreateCatalogImageRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Catalog Image Request Builder object.
     *
     * @param string $idempotencyKey
     * @param CatalogObject $image
     */
    public static function init(string $idempotencyKey, CatalogObject $image): self
    {
        return new self(new CreateCatalogImageRequest($idempotencyKey, $image));
    }

    /**
     * Sets object id field.
     *
     * @param string|null $value
     */
    public function objectId(?string $value): self
    {
        $this->instance->setObjectId($value);
        return $this;
    }

    /**
     * Sets is primary field.
     *
     * @param bool|null $value
     */
    public function isPrimary(?bool $value): self
    {
        $this->instance->setIsPrimary($value);
        return $this;
    }

    /**
     * Initializes a new Create Catalog Image Request object.
     */
    public function build(): CreateCatalogImageRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
