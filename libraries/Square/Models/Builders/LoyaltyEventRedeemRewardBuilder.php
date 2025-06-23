<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventRedeemReward;

/**
 * Builder for model LoyaltyEventRedeemReward
 *
 * @see LoyaltyEventRedeemReward
 */
class LoyaltyEventRedeemRewardBuilder
{
    /**
     * @var LoyaltyEventRedeemReward
     */
    private $instance;

    private function __construct(LoyaltyEventRedeemReward $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Redeem Reward Builder object.
     *
     * @param string $loyaltyProgramId
     */
    public static function init(string $loyaltyProgramId): self
    {
        return new self(new LoyaltyEventRedeemReward($loyaltyProgramId));
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
     * Sets order id field.
     *
     * @param string|null $value
     */
    public function orderId(?string $value): self
    {
        $this->instance->setOrderId($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Event Redeem Reward object.
     */
    public function build(): LoyaltyEventRedeemReward
    {
        return CoreHelper::clone($this->instance);
    }
}
