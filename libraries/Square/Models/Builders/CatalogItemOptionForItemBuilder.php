<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogItemOptionForItem;

/**
 * Builder for model CatalogItemOptionForItem
 *
 * @see CatalogItemOptionForItem
 */
class CatalogItemOptionForItemBuilder
{
    /**
     * @var CatalogItemOptionForItem
     */
    private $instance;

    private function __construct(CatalogItemOptionForItem $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Option For Item Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogItemOptionForItem());
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
     * Initializes a new Catalog Item Option For Item object.
     */
    public function build(): CatalogItemOptionForItem
    {
        return CoreHelper::clone($this->instance);
    }
}
