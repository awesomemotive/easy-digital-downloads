<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQuerySet;

/**
 * Builder for model CatalogQuerySet
 *
 * @see CatalogQuerySet
 */
class CatalogQuerySetBuilder
{
    /**
     * @var CatalogQuerySet
     */
    private $instance;

    private function __construct(CatalogQuerySet $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Set Builder object.
     *
     * @param string $attributeName
     * @param string[] $attributeValues
     */
    public static function init(string $attributeName, array $attributeValues): self
    {
        return new self(new CatalogQuerySet($attributeName, $attributeValues));
    }

    /**
     * Initializes a new Catalog Query Set object.
     */
    public function build(): CatalogQuerySet
    {
        return CoreHelper::clone($this->instance);
    }
}
