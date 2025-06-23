<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQuerySortedAttribute;

/**
 * Builder for model CatalogQuerySortedAttribute
 *
 * @see CatalogQuerySortedAttribute
 */
class CatalogQuerySortedAttributeBuilder
{
    /**
     * @var CatalogQuerySortedAttribute
     */
    private $instance;

    private function __construct(CatalogQuerySortedAttribute $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Sorted Attribute Builder object.
     *
     * @param string $attributeName
     */
    public static function init(string $attributeName): self
    {
        return new self(new CatalogQuerySortedAttribute($attributeName));
    }

    /**
     * Sets initial attribute value field.
     *
     * @param string|null $value
     */
    public function initialAttributeValue(?string $value): self
    {
        $this->instance->setInitialAttributeValue($value);
        return $this;
    }

    /**
     * Unsets initial attribute value field.
     */
    public function unsetInitialAttributeValue(): self
    {
        $this->instance->unsetInitialAttributeValue();
        return $this;
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
        return $this;
    }

    /**
     * Initializes a new Catalog Query Sorted Attribute object.
     */
    public function build(): CatalogQuerySortedAttribute
    {
        return CoreHelper::clone($this->instance);
    }
}
