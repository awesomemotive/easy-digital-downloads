<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventOther;

/**
 * Builder for model LoyaltyEventOther
 *
 * @see LoyaltyEventOther
 */
class LoyaltyEventOtherBuilder
{
    /**
     * @var LoyaltyEventOther
     */
    private $instance;

    private function __construct(LoyaltyEventOther $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Other Builder object.
     *
     * @param string $loyaltyProgramId
     * @param int $points
     */
    public static function init(string $loyaltyProgramId, int $points): self
    {
        return new self(new LoyaltyEventOther($loyaltyProgramId, $points));
    }

    /**
     * Initializes a new Loyalty Event Other object.
     */
    public function build(): LoyaltyEventOther
    {
        return CoreHelper::clone($this->instance);
    }
}
