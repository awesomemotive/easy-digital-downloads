<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates the gift card state.
 */
class GiftCardStatus
{
    /**
     * The gift card is active and can be used as a payment source.
     */
    public const ACTIVE = 'ACTIVE';

    /**
     * Any activity that changes the gift card balance is permanently forbidden.
     */
    public const DEACTIVATED = 'DEACTIVATED';

    /**
     * Any activity that changes the gift card balance is temporarily forbidden.
     */
    public const BLOCKED = 'BLOCKED';

    /**
     * The gift card is pending activation.
     * This is the initial state when a gift card is created. Typically, you'll call
     * [CreateGiftCardActivity]($e/GiftCardActivities/CreateGiftCardActivity) to create an
     * `ACTIVATE` activity that activates the gift card with an initial balance before first use.
     */
    public const PENDING = 'PENDING';
}
