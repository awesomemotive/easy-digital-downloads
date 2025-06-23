<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The food and beverage-specific details of a `FOOD_AND_BEV` item.
 */
class CatalogItemFoodAndBeverageDetails implements \JsonSerializable
{
    /**
     * @var array
     */
    private $calorieCount = [];

    /**
     * @var array
     */
    private $dietaryPreferences = [];

    /**
     * @var array
     */
    private $ingredients = [];

    /**
     * Returns Calorie Count.
     * The calorie count (in the unit of kcal) for the `FOOD_AND_BEV` type of items.
     */
    public function getCalorieCount(): ?int
    {
        if (count($this->calorieCount) == 0) {
            return null;
        }
        return $this->calorieCount['value'];
    }

    /**
     * Sets Calorie Count.
     * The calorie count (in the unit of kcal) for the `FOOD_AND_BEV` type of items.
     *
     * @maps calorie_count
     */
    public function setCalorieCount(?int $calorieCount): void
    {
        $this->calorieCount['value'] = $calorieCount;
    }

    /**
     * Unsets Calorie Count.
     * The calorie count (in the unit of kcal) for the `FOOD_AND_BEV` type of items.
     */
    public function unsetCalorieCount(): void
    {
        $this->calorieCount = [];
    }

    /**
     * Returns Dietary Preferences.
     * The dietary preferences for the `FOOD_AND_BEV` item.
     *
     * @return CatalogItemFoodAndBeverageDetailsDietaryPreference[]|null
     */
    public function getDietaryPreferences(): ?array
    {
        if (count($this->dietaryPreferences) == 0) {
            return null;
        }
        return $this->dietaryPreferences['value'];
    }

    /**
     * Sets Dietary Preferences.
     * The dietary preferences for the `FOOD_AND_BEV` item.
     *
     * @maps dietary_preferences
     *
     * @param CatalogItemFoodAndBeverageDetailsDietaryPreference[]|null $dietaryPreferences
     */
    public function setDietaryPreferences(?array $dietaryPreferences): void
    {
        $this->dietaryPreferences['value'] = $dietaryPreferences;
    }

    /**
     * Unsets Dietary Preferences.
     * The dietary preferences for the `FOOD_AND_BEV` item.
     */
    public function unsetDietaryPreferences(): void
    {
        $this->dietaryPreferences = [];
    }

    /**
     * Returns Ingredients.
     * The ingredients for the `FOOD_AND_BEV` type item.
     *
     * @return CatalogItemFoodAndBeverageDetailsIngredient[]|null
     */
    public function getIngredients(): ?array
    {
        if (count($this->ingredients) == 0) {
            return null;
        }
        return $this->ingredients['value'];
    }

    /**
     * Sets Ingredients.
     * The ingredients for the `FOOD_AND_BEV` type item.
     *
     * @maps ingredients
     *
     * @param CatalogItemFoodAndBeverageDetailsIngredient[]|null $ingredients
     */
    public function setIngredients(?array $ingredients): void
    {
        $this->ingredients['value'] = $ingredients;
    }

    /**
     * Unsets Ingredients.
     * The ingredients for the `FOOD_AND_BEV` type item.
     */
    public function unsetIngredients(): void
    {
        $this->ingredients = [];
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
        if (!empty($this->calorieCount)) {
            $json['calorie_count']       = $this->calorieCount['value'];
        }
        if (!empty($this->dietaryPreferences)) {
            $json['dietary_preferences'] = $this->dietaryPreferences['value'];
        }
        if (!empty($this->ingredients)) {
            $json['ingredients']         = $this->ingredients['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
