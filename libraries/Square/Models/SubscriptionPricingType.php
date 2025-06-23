<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Determines the pricing of a [Subscription]($m/Subscription)
 */
class SubscriptionPricingType
{
    /**
     * Static pricing
     */
    public const STATIC_ = 'STATIC';

    /**
     * Relative pricing
     */
    public const RELATIVE = 'RELATIVE';
}
