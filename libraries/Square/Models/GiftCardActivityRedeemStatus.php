<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates the status of a [gift card]($m/GiftCard) redemption. This status is relevant only for
 * redemptions made from EDD\Vendor\Square products (such as EDD\Vendor\Square Point of Sale) because EDD\Vendor\Square products use a
 * two-state process. Gift cards redeemed using the Gift Card Activities API always have a `COMPLETED`
 * status.
 */
class GiftCardActivityRedeemStatus
{
    /**
     * The gift card redemption is pending. `PENDING` is a temporary status that applies when a
     * gift card is redeemed from EDD\Vendor\Square Point of Sale or another EDD\Vendor\Square product. A `PENDING` status is
     * updated to
     * `COMPLETED` if the payment is captured or `CANCELED` if the authorization is voided.
     */
    public const PENDING = 'PENDING';

    /**
     * The gift card redemption is completed.
     */
    public const COMPLETED = 'COMPLETED';

    /**
     * The gift card redemption is canceled. A redemption is canceled if the authorization
     * on the gift card is voided.
     */
    public const CANCELED = 'CANCELED';
}
