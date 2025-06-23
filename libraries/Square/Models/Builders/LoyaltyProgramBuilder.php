<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyProgram;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRule;
use EDD\Vendor\Square\Models\LoyaltyProgramExpirationPolicy;
use EDD\Vendor\Square\Models\LoyaltyProgramRewardTier;
use EDD\Vendor\Square\Models\LoyaltyProgramTerminology;

/**
 * Builder for model LoyaltyProgram
 *
 * @see LoyaltyProgram
 */
class LoyaltyProgramBuilder
{
    /**
     * @var LoyaltyProgram
     */
    private $instance;

    private function __construct(LoyaltyProgram $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Program Builder object.
     */
    public static function init(): self
    {
        return new self(new LoyaltyProgram());
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
     * Sets reward tiers field.
     *
     * @param LoyaltyProgramRewardTier[]|null $value
     */
    public function rewardTiers(?array $value): self
    {
        $this->instance->setRewardTiers($value);
        return $this;
    }

    /**
     * Unsets reward tiers field.
     */
    public function unsetRewardTiers(): self
    {
        $this->instance->unsetRewardTiers();
        return $this;
    }

    /**
     * Sets expiration policy field.
     *
     * @param LoyaltyProgramExpirationPolicy|null $value
     */
    public function expirationPolicy(?LoyaltyProgramExpirationPolicy $value): self
    {
        $this->instance->setExpirationPolicy($value);
        return $this;
    }

    /**
     * Sets terminology field.
     *
     * @param LoyaltyProgramTerminology|null $value
     */
    public function terminology(?LoyaltyProgramTerminology $value): self
    {
        $this->instance->setTerminology($value);
        return $this;
    }

    /**
     * Sets location ids field.
     *
     * @param string[]|null $value
     */
    public function locationIds(?array $value): self
    {
        $this->instance->setLocationIds($value);
        return $this;
    }

    /**
     * Unsets location ids field.
     */
    public function unsetLocationIds(): self
    {
        $this->instance->unsetLocationIds();
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
     * Sets accrual rules field.
     *
     * @param LoyaltyProgramAccrualRule[]|null $value
     */
    public function accrualRules(?array $value): self
    {
        $this->instance->setAccrualRules($value);
        return $this;
    }

    /**
     * Unsets accrual rules field.
     */
    public function unsetAccrualRules(): self
    {
        $this->instance->unsetAccrualRules();
        return $this;
    }

    /**
     * Initializes a new Loyalty Program object.
     */
    public function build(): LoyaltyProgram
    {
        return CoreHelper::clone($this->instance);
    }
}
