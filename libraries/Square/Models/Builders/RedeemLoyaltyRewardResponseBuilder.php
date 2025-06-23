<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\LoyaltyEvent;
use EDD\Vendor\Square\Models\RedeemLoyaltyRewardResponse;

/**
 * Builder for model RedeemLoyaltyRewardResponse
 *
 * @see RedeemLoyaltyRewardResponse
 */
class RedeemLoyaltyRewardResponseBuilder
{
    /**
     * @var RedeemLoyaltyRewardResponse
     */
    private $instance;

    private function __construct(RedeemLoyaltyRewardResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Redeem Loyalty Reward Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RedeemLoyaltyRewardResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets event field.
     *
     * @param LoyaltyEvent|null $value
     */
    public function event(?LoyaltyEvent $value): self
    {
        $this->instance->setEvent($value);
        return $this;
    }

    /**
     * Initializes a new Redeem Loyalty Reward Response object.
     */
    public function build(): RedeemLoyaltyRewardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
