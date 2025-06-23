<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventLocationFilter;

/**
 * Builder for model LoyaltyEventLocationFilter
 *
 * @see LoyaltyEventLocationFilter
 */
class LoyaltyEventLocationFilterBuilder
{
    /**
     * @var LoyaltyEventLocationFilter
     */
    private $instance;

    private function __construct(LoyaltyEventLocationFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Location Filter Builder object.
     *
     * @param string[] $locationIds
     */
    public static function init(array $locationIds): self
    {
        return new self(new LoyaltyEventLocationFilter($locationIds));
    }

    /**
     * Initializes a new Loyalty Event Location Filter object.
     */
    public function build(): LoyaltyEventLocationFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
