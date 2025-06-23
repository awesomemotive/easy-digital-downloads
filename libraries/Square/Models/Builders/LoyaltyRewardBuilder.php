<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyReward;

/**
 * Builder for model LoyaltyReward
 *
 * @see LoyaltyReward
 */
class LoyaltyRewardBuilder
{
    /**
     * @var LoyaltyReward
     */
    private $instance;

    private function __construct(LoyaltyReward $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Reward Builder object.
     *
     * @param string $loyaltyAccountId
     * @param string $rewardTierId
     */
    public static function init(string $loyaltyAccountId, string $rewardTierId): self
    {
        return new self(new LoyaltyReward($loyaltyAccountId, $rewardTierId));
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets points field.
     *
     * @param int|null $value
     */
    public function points(?int $value): self
    {
        $this->instance->setPoints($value);
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
     * Unsets order id field.
     */
    public function unsetOrderId(): self
    {
        $this->instance->unsetOrderId();
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets redeemed at field.
     *
     * @param string|null $value
     */
    public function redeemedAt(?string $value): self
    {
        $this->instance->setRedeemedAt($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Reward object.
     */
    public function build(): LoyaltyReward
    {
        return CoreHelper::clone($this->instance);
    }
}
