<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogItemFoodAndBeverageDetailsDietaryPreference;

/**
 * Builder for model CatalogItemFoodAndBeverageDetailsDietaryPreference
 *
 * @see CatalogItemFoodAndBeverageDetailsDietaryPreference
 */
class CatalogItemFoodAndBeverageDetailsDietaryPreferenceBuilder
{
    /**
     * @var CatalogItemFoodAndBeverageDetailsDietaryPreference
     */
    private $instance;

    private function __construct(CatalogItemFoodAndBeverageDetailsDietaryPreference $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Food And Beverage Details Dietary Preference Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogItemFoodAndBeverageDetailsDietaryPreference());
    }

    /**
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Sets standard name field.
     *
     * @param string|null $value
     */
    public function standardName(?string $value): self
    {
        $this->instance->setStandardName($value);
        return $this;
    }

    /**
     * Sets custom name field.
     *
     * @param string|null $value
     */
    public function customName(?string $value): self
    {
        $this->instance->setCustomName($value);
        return $this;
    }

    /**
     * Unsets custom name field.
     */
    public function unsetCustomName(): self
    {
        $this->instance->unsetCustomName();
        return $this;
    }

    /**
     * Initializes a new Catalog Item Food And Beverage Details Dietary Preference object.
     */
    public function build(): CatalogItemFoodAndBeverageDetailsDietaryPreference
    {
        return CoreHelper::clone($this->instance);
    }
}
