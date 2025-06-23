<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinition;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinitionNumberConfig;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinitionSelectionConfig;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinitionStringConfig;
use EDD\Vendor\Square\Models\SourceApplication;

/**
 * Builder for model CatalogCustomAttributeDefinition
 *
 * @see CatalogCustomAttributeDefinition
 */
class CatalogCustomAttributeDefinitionBuilder
{
    /**
     * @var CatalogCustomAttributeDefinition
     */
    private $instance;

    private function __construct(CatalogCustomAttributeDefinition $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Custom Attribute Definition Builder object.
     *
     * @param string $type
     * @param string $name
     * @param string[] $allowedObjectTypes
     */
    public static function init(string $type, string $name, array $allowedObjectTypes): self
    {
        return new self(new CatalogCustomAttributeDefinition($type, $name, $allowedObjectTypes));
    }

    /**
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Unsets description field.
     */
    public function unsetDescription(): self
    {
        $this->instance->unsetDescription();
        return $this;
    }

    /**
     * Sets source application field.
     *
     * @param SourceApplication|null $value
     */
    public function sourceApplication(?SourceApplication $value): self
    {
        $this->instance->setSourceApplication($value);
        return $this;
    }

    /**
     * Sets seller visibility field.
     *
     * @param string|null $value
     */
    public function sellerVisibility(?string $value): self
    {
        $this->instance->setSellerVisibility($value);
        return $this;
    }

    /**
     * Sets app visibility field.
     *
     * @param string|null $value
     */
    public function appVisibility(?string $value): self
    {
        $this->instance->setAppVisibility($value);
        return $this;
    }

    /**
     * Sets string config field.
     *
     * @param CatalogCustomAttributeDefinitionStringConfig|null $value
     */
    public function stringConfig(?CatalogCustomAttributeDefinitionStringConfig $value): self
    {
        $this->instance->setStringConfig($value);
        return $this;
    }

    /**
     * Sets number config field.
     *
     * @param CatalogCustomAttributeDefinitionNumberConfig|null $value
     */
    public function numberConfig(?CatalogCustomAttributeDefinitionNumberConfig $value): self
    {
        $this->instance->setNumberConfig($value);
        return $this;
    }

    /**
     * Sets selection config field.
     *
     * @param CatalogCustomAttributeDefinitionSelectionConfig|null $value
     */
    public function selectionConfig(?CatalogCustomAttributeDefinitionSelectionConfig $value): self
    {
        $this->instance->setSelectionConfig($value);
        return $this;
    }

    /**
     * Sets custom attribute usage count field.
     *
     * @param int|null $value
     */
    public function customAttributeUsageCount(?int $value): self
    {
        $this->instance->setCustomAttributeUsageCount($value);
        return $this;
    }

    /**
     * Sets key field.
     *
     * @param string|null $value
     */
    public function key(?string $value): self
    {
        $this->instance->setKey($value);
        return $this;
    }

    /**
     * Unsets key field.
     */
    public function unsetKey(): self
    {
        $this->instance->unsetKey();
        return $this;
    }

    /**
     * Initializes a new Catalog Custom Attribute Definition object.
     */
    public function build(): CatalogCustomAttributeDefinition
    {
        return CoreHelper::clone($this->instance);
    }
}
