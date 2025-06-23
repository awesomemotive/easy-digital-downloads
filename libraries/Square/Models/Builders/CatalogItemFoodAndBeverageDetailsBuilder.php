<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogItemFoodAndBeverageDetails;
use EDD\Vendor\Square\Models\CatalogItemFoodAndBeverageDetailsDietaryPreference;
use EDD\Vendor\Square\Models\CatalogItemFoodAndBeverageDetailsIngredient;

/**
 * Builder for model CatalogItemFoodAndBeverageDetails
 *
 * @see CatalogItemFoodAndBeverageDetails
 */
class CatalogItemFoodAndBeverageDetailsBuilder
{
    /**
     * @var CatalogItemFoodAndBeverageDetails
     */
    private $instance;

    private function __construct(CatalogItemFoodAndBeverageDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Food And Beverage Details Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogItemFoodAndBeverageDetails());
    }

    /**
     * Sets calorie count field.
     *
     * @param int|null $value
     */
    public function calorieCount(?int $value): self
    {
        $this->instance->setCalorieCount($value);
        return $this;
    }

    /**
     * Unsets calorie count field.
     */
    public function unsetCalorieCount(): self
    {
        $this->instance->unsetCalorieCount();
        return $this;
    }

    /**
     * Sets dietary preferences field.
     *
     * @param CatalogItemFoodAndBeverageDetailsDietaryPreference[]|null $value
     */
    public function dietaryPreferences(?array $value): self
    {
        $this->instance->setDietaryPreferences($value);
        return $this;
    }

    /**
     * Unsets dietary preferences field.
     */
    public function unsetDietaryPreferences(): self
    {
        $this->instance->unsetDietaryPreferences();
        return $this;
    }

    /**
     * Sets ingredients field.
     *
     * @param CatalogItemFoodAndBeverageDetailsIngredient[]|null $value
     */
    public function ingredients(?array $value): self
    {
        $this->instance->setIngredients($value);
        return $this;
    }

    /**
     * Unsets ingredients field.
     */
    public function unsetIngredients(): self
    {
        $this->instance->unsetIngredients();
        return $this;
    }

    /**
     * Initializes a new Catalog Item Food And Beverage Details object.
     */
    public function build(): CatalogItemFoodAndBeverageDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
