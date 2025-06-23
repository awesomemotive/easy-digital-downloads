<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * The type of a CatalogItem. Connect V2 only allows the creation of `REGULAR` or
 * `APPOINTMENTS_SERVICE` items.
 */
class CatalogItemProductType
{
    /**
     * An ordinary item.
     */
    public const REGULAR = 'REGULAR';

    /**
     * A EDD\Vendor\Square gift card.
     */
    public const GIFT_CARD = 'GIFT_CARD';

    /**
     * A service that can be booked using the EDD\Vendor\Square Appointments app.
     */
    public const APPOINTMENTS_SERVICE = 'APPOINTMENTS_SERVICE';

    /**
     * A food or beverage item that can be sold by restaurants and other food venues.
     */
    public const FOOD_AND_BEV = 'FOOD_AND_BEV';

    /**
     * An event which tickets can be sold for, including location, address, and times.
     */
    public const EVENT = 'EVENT';

    /**
     * A digital item like an ebook or song.
     */
    public const DIGITAL = 'DIGITAL';

    /**
     * A donation which site visitors can send for any cause.
     */
    public const DONATION = 'DONATION';

    /**
     * A legacy EDD\Vendor\Square Online service that is manually fulfilled. This corresponds to the `Other` item type
     * displayed in the EDD\Vendor\Square Seller Dashboard and EDD\Vendor\Square POS apps.
     */
    public const LEGACY_SQUARE_ONLINE_SERVICE = 'LEGACY_SQUARE_ONLINE_SERVICE';

    /**
     * A legacy EDD\Vendor\Square Online membership that is manually fulfilled. This corresponds to the `Membership`
     * item type displayed in the EDD\Vendor\Square Seller Dashboard and EDD\Vendor\Square POS apps.
     */
    public const LEGACY_SQUARE_ONLINE_MEMBERSHIP = 'LEGACY_SQUARE_ONLINE_MEMBERSHIP';
}
