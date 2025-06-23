<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventCreateReward;

/**
 * Builder for model LoyaltyEventCreateReward
 *
 * @see LoyaltyEventCreateReward
 */
class LoyaltyEventCreateRewardBuilder
{
    /**
     * @var LoyaltyEventCreateReward
     */
    private $instance;

    private function __construct(LoyaltyEventCreateReward $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Create Reward Builder object.
     *
     * @param string $loyaltyProgramId
     * @param int $points
     */
    public static function init(string $loyaltyProgramId, int $points): self
    {
        return new self(new LoyaltyEventCreateReward($loyaltyProgramId, $points));
    }

    /**
     * Sets reward id field.
     *
     * @param string|null $value
     */
    public function rewardId(?string $value): self
    {
        $this->instance->setRewardId($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Event Create Reward object.
     */
    public function build(): LoyaltyEventCreateReward
    {
        return CoreHelper::clone($this->instance);
    }
}
