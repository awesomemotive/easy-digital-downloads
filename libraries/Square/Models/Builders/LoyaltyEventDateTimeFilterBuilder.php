<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventDateTimeFilter;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model LoyaltyEventDateTimeFilter
 *
 * @see LoyaltyEventDateTimeFilter
 */
class LoyaltyEventDateTimeFilterBuilder
{
    /**
     * @var LoyaltyEventDateTimeFilter
     */
    private $instance;

    private function __construct(LoyaltyEventDateTimeFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Date Time Filter Builder object.
     *
     * @param TimeRange $createdAt
     */
    public static function init(TimeRange $createdAt): self
    {
        return new self(new LoyaltyEventDateTimeFilter($createdAt));
    }

    /**
     * Initializes a new Loyalty Event Date Time Filter object.
     */
    public function build(): LoyaltyEventDateTimeFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
