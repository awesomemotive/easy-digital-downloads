<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ItemVariationLocationOverrides;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model ItemVariationLocationOverrides
 *
 * @see ItemVariationLocationOverrides
 */
class ItemVariationLocationOverridesBuilder
{
    /**
     * @var ItemVariationLocationOverrides
     */
    private $instance;

    private function __construct(ItemVariationLocationOverrides $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Item Variation Location Overrides Builder object.
     */
    public static function init(): self
    {
        return new self(new ItemVariationLocationOverrides());
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
     * Sets price money field.
     *
     * @param Money|null $value
     */
    public function priceMoney(?Money $value): self
    {
        $this->instance->setPriceMoney($value);
        return $this;
    }

    /**
     * Sets pricing type field.
     *
     * @param string|null $value
     */
    public function pricingType(?string $value): self
    {
        $this->instance->setPricingType($value);
        return $this;
    }

    /**
     * Sets track inventory field.
     *
     * @param bool|null $value
     */
    public function trackInventory(?bool $value): self
    {
        $this->instance->setTrackInventory($value);
        return $this;
    }

    /**
     * Unsets track inventory field.
     */
    public function unsetTrackInventory(): self
    {
        $this->instance->unsetTrackInventory();
        return $this;
    }

    /**
     * Sets inventory alert type field.
     *
     * @param string|null $value
     */
    public function inventoryAlertType(?string $value): self
    {
        $this->instance->setInventoryAlertType($value);
        return $this;
    }

    /**
     * Sets inventory alert threshold field.
     *
     * @param int|null $value
     */
    public function inventoryAlertThreshold(?int $value): self
    {
        $this->instance->setInventoryAlertThreshold($value);
        return $this;
    }

    /**
     * Unsets inventory alert threshold field.
     */
    public function unsetInventoryAlertThreshold(): self
    {
        $this->instance->unsetInventoryAlertThreshold();
        return $this;
    }

    /**
     * Sets sold out field.
     *
     * @param bool|null $value
     */
    public function soldOut(?bool $value): self
    {
        $this->instance->setSoldOut($value);
        return $this;
    }

    /**
     * Sets sold out valid until field.
     *
     * @param string|null $value
     */
    public function soldOutValidUntil(?string $value): self
    {
        $this->instance->setSoldOutValidUntil($value);
        return $this;
    }

    /**
     * Initializes a new Item Variation Location Overrides object.
     */
    public function build(): ItemVariationLocationOverrides
    {
        return CoreHelper::clone($this->instance);
    }
}
