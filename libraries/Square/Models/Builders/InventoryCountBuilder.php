<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\InventoryCount;

/**
 * Builder for model InventoryCount
 *
 * @see InventoryCount
 */
class InventoryCountBuilder
{
    /**
     * @var InventoryCount
     */
    private $instance;

    private function __construct(InventoryCount $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Inventory Count Builder object.
     */
    public static function init(): self
    {
        return new self(new InventoryCount());
    }

    /**
     * Sets catalog object id field.
     *
     * @param string|null $value
     */
    public function catalogObjectId(?string $value): self
    {
        $this->instance->setCatalogObjectId($value);
        return $this;
    }

    /**
     * Unsets catalog object id field.
     */
    public function unsetCatalogObjectId(): self
    {
        $this->instance->unsetCatalogObjectId();
        return $this;
    }

    /**
     * Sets catalog object type field.
     *
     * @param string|null $value
     */
    public function catalogObjectType(?string $value): self
    {
        $this->instance->setCatalogObjectType($value);
        return $this;
    }

    /**
     * Unsets catalog object type field.
     */
    public function unsetCatalogObjectType(): self
    {
        $this->instance->unsetCatalogObjectType();
        return $this;
    }

    /**
     * Sets state field.
     *
     * @param string|null $value
     */
    public function state(?string $value): self
    {
        $this->instance->setState($value);
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
     * Sets quantity field.
     *
     * @param string|null $value
     */
    public function quantity(?string $value): self
    {
        $this->instance->setQuantity($value);
        return $this;
    }

    /**
     * Unsets quantity field.
     */
    public function unsetQuantity(): self
    {
        $this->instance->unsetQuantity();
        return $this;
    }

    /**
     * Sets calculated at field.
     *
     * @param string|null $value
     */
    public function calculatedAt(?string $value): self
    {
        $this->instance->setCalculatedAt($value);
        return $this;
    }

    /**
     * Sets is estimated field.
     *
     * @param bool|null $value
     */
    public function isEstimated(?bool $value): self
    {
        $this->instance->setIsEstimated($value);
        return $this;
    }

    /**
     * Initializes a new Inventory Count object.
     */
    public function build(): InventoryCount
    {
        return CoreHelper::clone($this->instance);
    }
}
