<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates the type of a category.
 */
class CatalogCategoryType
{
    /**
     * The regular category.
     */
    public const REGULAR_CATEGORY = 'REGULAR_CATEGORY';

    /**
     * The menu category.
     */
    public const MENU_CATEGORY = 'MENU_CATEGORY';

    /**
     * Kitchen categories are used by KDS (Kitchen Display System) to route items to specific clients
     */
    public const KITCHEN_CATEGORY = 'KITCHEN_CATEGORY';
}
