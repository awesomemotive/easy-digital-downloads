<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventDeleteReward;

/**
 * Builder for model LoyaltyEventDeleteReward
 *
 * @see LoyaltyEventDeleteReward
 */
class LoyaltyEventDeleteRewardBuilder
{
    /**
     * @var LoyaltyEventDeleteReward
     */
    private $instance;

    private function __construct(LoyaltyEventDeleteReward $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Delete Reward Builder object.
     *
     * @param string $loyaltyProgramId
     * @param int $points
     */
    public static function init(string $loyaltyProgramId, int $points): self
    {
        return new self(new LoyaltyEventDeleteReward($loyaltyProgramId, $points));
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
     * Initializes a new Loyalty Event Delete Reward object.
     */
    public function build(): LoyaltyEventDeleteReward
    {
        return CoreHelper::clone($this->instance);
    }
}
