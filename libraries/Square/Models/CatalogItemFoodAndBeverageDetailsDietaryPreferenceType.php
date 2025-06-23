<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * The type of dietary preference for the `FOOD_AND_BEV` type of items and integredients.
 */
class CatalogItemFoodAndBeverageDetailsDietaryPreferenceType
{
    /**
     * A standard value from a pre-determined list.
     */
    public const STANDARD = 'STANDARD';

    /**
     * A user-defined custom value.
     */
    public const CUSTOM = 'CUSTOM';
}
