<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Defines the visibility of a custom attribute to sellers in EDD\Vendor\Square
 * client applications, EDD\Vendor\Square APIs or in EDD\Vendor\Square UIs (including EDD\Vendor\Square Point
 * of Sale applications and EDD\Vendor\Square Dashboard).
 */
class CatalogCustomAttributeDefinitionSellerVisibility
{
    /**
     * Sellers cannot read this custom attribute in EDD\Vendor\Square client
     * applications or EDD\Vendor\Square APIs.
     */
    public const SELLER_VISIBILITY_HIDDEN = 'SELLER_VISIBILITY_HIDDEN';

    /**
     * Sellers can read and write this custom attribute value in catalog objects,
     * but cannot edit the custom attribute definition.
     */
    public const SELLER_VISIBILITY_READ_WRITE_VALUES = 'SELLER_VISIBILITY_READ_WRITE_VALUES';
}
