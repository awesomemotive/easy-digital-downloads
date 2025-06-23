<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogV1Id;

/**
 * Builder for model CatalogV1Id
 *
 * @see CatalogV1Id
 */
class CatalogV1IdBuilder
{
    /**
     * @var CatalogV1Id
     */
    private $instance;

    private function __construct(CatalogV1Id $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog V1 Id Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogV1Id());
    }

    /**
     * Sets catalog v 1 id field.
     *
     * @param string|null $value
     */
    public function catalogV1Id(?string $value): self
    {
        $this->instance->setCatalogV1Id($value);
        return $this;
    }

    /**
     * Unsets catalog v 1 id field.
     */
    public function unsetCatalogV1Id(): self
    {
        $this->instance->unsetCatalogV1Id();
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Initializes a new Catalog V1 Id object.
     */
    public function build(): CatalogV1Id
    {
        return CoreHelper::clone($this->instance);
    }
}
