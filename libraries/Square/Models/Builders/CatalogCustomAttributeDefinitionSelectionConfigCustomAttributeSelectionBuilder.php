<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection;

/**
 * Builder for model CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection
 *
 * @see CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection
 */
class CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelectionBuilder
{
    /**
     * @var CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection
     */
    private $instance;

    private function __construct(CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Custom Attribute Definition Selection Config Custom Attribute Selection
     * Builder object.
     *
     * @param string $name
     */
    public static function init(string $name): self
    {
        return new self(new CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection($name));
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
        return $this;
    }

    /**
     * Initializes a new Catalog Custom Attribute Definition Selection Config Custom Attribute Selection
     * object.
     */
    public function build(): CatalogCustomAttributeDefinitionSelectionConfigCustomAttributeSelection
    {
        return CoreHelper::clone($this->instance);
    }
}
