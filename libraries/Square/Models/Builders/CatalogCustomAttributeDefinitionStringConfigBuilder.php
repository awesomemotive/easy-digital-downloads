<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogCustomAttributeDefinitionStringConfig;

/**
 * Builder for model CatalogCustomAttributeDefinitionStringConfig
 *
 * @see CatalogCustomAttributeDefinitionStringConfig
 */
class CatalogCustomAttributeDefinitionStringConfigBuilder
{
    /**
     * @var CatalogCustomAttributeDefinitionStringConfig
     */
    private $instance;

    private function __construct(CatalogCustomAttributeDefinitionStringConfig $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Custom Attribute Definition String Config Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogCustomAttributeDefinitionStringConfig());
    }

    /**
     * Sets enforce uniqueness field.
     *
     * @param bool|null $value
     */
    public function enforceUniqueness(?bool $value): self
    {
        $this->instance->setEnforceUniqueness($value);
        return $this;
    }

    /**
     * Unsets enforce uniqueness field.
     */
    public function unsetEnforceUniqueness(): self
    {
        $this->instance->unsetEnforceUniqueness();
        return $this;
    }

    /**
     * Initializes a new Catalog Custom Attribute Definition String Config object.
     */
    public function build(): CatalogCustomAttributeDefinitionStringConfig
    {
        return CoreHelper::clone($this->instance);
    }
}
