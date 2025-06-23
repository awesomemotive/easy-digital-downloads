<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateLoyaltyRewardRequest;
use EDD\Vendor\Square\Models\LoyaltyReward;

/**
 * Builder for model CreateLoyaltyRewardRequest
 *
 * @see CreateLoyaltyRewardRequest
 */
class CreateLoyaltyRewardRequestBuilder
{
    /**
     * @var CreateLoyaltyRewardRequest
     */
    private $instance;

    private function __construct(CreateLoyaltyRewardRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Loyalty Reward Request Builder object.
     *
     * @param LoyaltyReward $reward
     * @param string $idempotencyKey
     */
    public static function init(LoyaltyReward $reward, string $idempotencyKey): self
    {
        return new self(new CreateLoyaltyRewardRequest($reward, $idempotencyKey));
    }

    /**
     * Initializes a new Create Loyalty Reward Request object.
     */
    public function build(): CreateLoyaltyRewardRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
