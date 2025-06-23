<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObjectReference;
use EDD\Vendor\Square\Models\LoyaltyProgramRewardDefinition;
use EDD\Vendor\Square\Models\LoyaltyProgramRewardTier;

/**
 * Builder for model LoyaltyProgramRewardTier
 *
 * @see LoyaltyProgramRewardTier
 */
class LoyaltyProgramRewardTierBuilder
{
    /**
     * @var LoyaltyProgramRewardTier
     */
    private $instance;

    private function __construct(LoyaltyProgramRewardTier $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Program Reward Tier Builder object.
     *
     * @param int $points
     * @param CatalogObjectReference $pricingRuleReference
     */
    public static function init(int $points, CatalogObjectReference $pricingRuleReference): self
    {
        return new self(new LoyaltyProgramRewardTier($points, $pricingRuleReference));
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
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Sets definition field.
     *
     * @param LoyaltyProgramRewardDefinition|null $value
     */
    public function definition(?LoyaltyProgramRewardDefinition $value): self
    {
        $this->instance->setDefinition($value);
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
     * Initializes a new Loyalty Program Reward Tier object.
     */
    public function build(): LoyaltyProgramRewardTier
    {
        return CoreHelper::clone($this->instance);
    }
}
