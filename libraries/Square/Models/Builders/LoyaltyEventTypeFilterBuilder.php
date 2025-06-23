<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventTypeFilter;

/**
 * Builder for model LoyaltyEventTypeFilter
 *
 * @see LoyaltyEventTypeFilter
 */
class LoyaltyEventTypeFilterBuilder
{
    /**
     * @var LoyaltyEventTypeFilter
     */
    private $instance;

    private function __construct(LoyaltyEventTypeFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Type Filter Builder object.
     *
     * @param string[] $types
     */
    public static function init(array $types): self
    {
        return new self(new LoyaltyEventTypeFilter($types));
    }

    /**
     * Initializes a new Loyalty Event Type Filter object.
     */
    public function build(): LoyaltyEventTypeFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
