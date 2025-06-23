<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogItemOptionValueForItemVariation;

/**
 * Builder for model CatalogItemOptionValueForItemVariation
 *
 * @see CatalogItemOptionValueForItemVariation
 */
class CatalogItemOptionValueForItemVariationBuilder
{
    /**
     * @var CatalogItemOptionValueForItemVariation
     */
    private $instance;

    private function __construct(CatalogItemOptionValueForItemVariation $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Option Value For Item Variation Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogItemOptionValueForItemVariation());
    }

    /**
     * Sets item option id field.
     *
     * @param string|null $value
     */
    public function itemOptionId(?string $value): self
    {
        $this->instance->setItemOptionId($value);
        return $this;
    }

    /**
     * Unsets item option id field.
     */
    public function unsetItemOptionId(): self
    {
        $this->instance->unsetItemOptionId();
        return $this;
    }

    /**
     * Sets item option value id field.
     *
     * @param string|null $value
     */
    public function itemOptionValueId(?string $value): self
    {
        $this->instance->setItemOptionValueId($value);
        return $this;
    }

    /**
     * Unsets item option value id field.
     */
    public function unsetItemOptionValueId(): self
    {
        $this->instance->unsetItemOptionValueId();
        return $this;
    }

    /**
     * Initializes a new Catalog Item Option Value For Item Variation object.
     */
    public function build(): CatalogItemOptionValueForItemVariation
    {
        return CoreHelper::clone($this->instance);
    }
}
