<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Dietary preferences that can be assigned to an `FOOD_AND_BEV` item and its ingredients.
 */
class CatalogItemFoodAndBeverageDetailsDietaryPreference implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $standardName;

    /**
     * @var array
     */
    private $customName = [];

    /**
     * Returns Type.
     * The type of dietary preference for the `FOOD_AND_BEV` type of items and integredients.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * The type of dietary preference for the `FOOD_AND_BEV` type of items and integredients.
     *
     * @maps type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Standard Name.
     * Standard dietary preferences for food and beverage items that are recommended on item creation.
     */
    public function getStandardName(): ?string
    {
        return $this->standardName;
    }

    /**
     * Sets Standard Name.
     * Standard dietary preferences for food and beverage items that are recommended on item creation.
     *
     * @maps standard_name
     */
    public function setStandardName(?string $standardName): void
    {
        $this->standardName = $standardName;
    }

    /**
     * Returns Custom Name.
     * The name of a user-defined custom dietary preference. This should be null if it's a standard dietary
     * preference.
     */
    public function getCustomName(): ?string
    {
        if (count($this->customName) == 0) {
            return null;
        }
        return $this->customName['value'];
    }

    /**
     * Sets Custom Name.
     * The name of a user-defined custom dietary preference. This should be null if it's a standard dietary
     * preference.
     *
     * @maps custom_name
     */
    public function setCustomName(?string $customName): void
    {
        $this->customName['value'] = $customName;
    }

    /**
     * Unsets Custom Name.
     * The name of a user-defined custom dietary preference. This should be null if it's a standard dietary
     * preference.
     */
    public function unsetCustomName(): void
    {
        $this->customName = [];
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (isset($this->type)) {
            $json['type']          = $this->type;
        }
        if (isset($this->standardName)) {
            $json['standard_name'] = $this->standardName;
        }
        if (!empty($this->customName)) {
            $json['custom_name']   = $this->customName['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
