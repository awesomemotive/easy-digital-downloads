<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventExpirePoints;

/**
 * Builder for model LoyaltyEventExpirePoints
 *
 * @see LoyaltyEventExpirePoints
 */
class LoyaltyEventExpirePointsBuilder
{
    /**
     * @var LoyaltyEventExpirePoints
     */
    private $instance;

    private function __construct(LoyaltyEventExpirePoints $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Expire Points Builder object.
     *
     * @param string $loyaltyProgramId
     * @param int $points
     */
    public static function init(string $loyaltyProgramId, int $points): self
    {
        return new self(new LoyaltyEventExpirePoints($loyaltyProgramId, $points));
    }

    /**
     * Initializes a new Loyalty Event Expire Points object.
     */
    public function build(): LoyaltyEventExpirePoints
    {
        return CoreHelper::clone($this->instance);
    }
}
