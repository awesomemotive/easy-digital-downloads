<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQueryItemVariationsForItemOptionValues;

/**
 * Builder for model CatalogQueryItemVariationsForItemOptionValues
 *
 * @see CatalogQueryItemVariationsForItemOptionValues
 */
class CatalogQueryItemVariationsForItemOptionValuesBuilder
{
    /**
     * @var CatalogQueryItemVariationsForItemOptionValues
     */
    private $instance;

    private function __construct(CatalogQueryItemVariationsForItemOptionValues $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Item Variations For Item Option Values Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogQueryItemVariationsForItemOptionValues());
    }

    /**
     * Sets item option value ids field.
     *
     * @param string[]|null $value
     */
    public function itemOptionValueIds(?array $value): self
    {
        $this->instance->setItemOptionValueIds($value);
        return $this;
    }

    /**
     * Unsets item option value ids field.
     */
    public function unsetItemOptionValueIds(): self
    {
        $this->instance->unsetItemOptionValueIds();
        return $this;
    }

    /**
     * Initializes a new Catalog Query Item Variations For Item Option Values object.
     */
    public function build(): CatalogQueryItemVariationsForItemOptionValues
    {
        return CoreHelper::clone($this->instance);
    }
}
