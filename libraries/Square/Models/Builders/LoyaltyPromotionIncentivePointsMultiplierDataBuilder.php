<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyPromotionIncentivePointsMultiplierData;

/**
 * Builder for model LoyaltyPromotionIncentivePointsMultiplierData
 *
 * @see LoyaltyPromotionIncentivePointsMultiplierData
 */
class LoyaltyPromotionIncentivePointsMultiplierDataBuilder
{
    /**
     * @var LoyaltyPromotionIncentivePointsMultiplierData
     */
    private $instance;

    private function __construct(LoyaltyPromotionIncentivePointsMultiplierData $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Promotion Incentive Points Multiplier Data Builder object.
     */
    public static function init(): self
    {
        return new self(new LoyaltyPromotionIncentivePointsMultiplierData());
    }

    /**
     * Sets points multiplier field.
     *
     * @param int|null $value
     */
    public function pointsMultiplier(?int $value): self
    {
        $this->instance->setPointsMultiplier($value);
        return $this;
    }

    /**
     * Unsets points multiplier field.
     */
    public function unsetPointsMultiplier(): self
    {
        $this->instance->unsetPointsMultiplier();
        return $this;
    }

    /**
     * Sets multiplier field.
     *
     * @param string|null $value
     */
    public function multiplier(?string $value): self
    {
        $this->instance->setMultiplier($value);
        return $this;
    }

    /**
     * Unsets multiplier field.
     */
    public function unsetMultiplier(): self
    {
        $this->instance->unsetMultiplier();
        return $this;
    }

    /**
     * Initializes a new Loyalty Promotion Incentive Points Multiplier Data object.
     */
    public function build(): LoyaltyPromotionIncentivePointsMultiplierData
    {
        return CoreHelper::clone($this->instance);
    }
}
