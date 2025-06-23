<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyPromotionIncentive;
use EDD\Vendor\Square\Models\LoyaltyPromotionIncentivePointsAdditionData;
use EDD\Vendor\Square\Models\LoyaltyPromotionIncentivePointsMultiplierData;

/**
 * Builder for model LoyaltyPromotionIncentive
 *
 * @see LoyaltyPromotionIncentive
 */
class LoyaltyPromotionIncentiveBuilder
{
    /**
     * @var LoyaltyPromotionIncentive
     */
    private $instance;

    private function __construct(LoyaltyPromotionIncentive $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Promotion Incentive Builder object.
     *
     * @param string $type
     */
    public static function init(string $type): self
    {
        return new self(new LoyaltyPromotionIncentive($type));
    }

    /**
     * Sets points multiplier data field.
     *
     * @param LoyaltyPromotionIncentivePointsMultiplierData|null $value
     */
    public function pointsMultiplierData(?LoyaltyPromotionIncentivePointsMultiplierData $value): self
    {
        $this->instance->setPointsMultiplierData($value);
        return $this;
    }

    /**
     * Sets points addition data field.
     *
     * @param LoyaltyPromotionIncentivePointsAdditionData|null $value
     */
    public function pointsAdditionData(?LoyaltyPromotionIncentivePointsAdditionData $value): self
    {
        $this->instance->setPointsAdditionData($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Promotion Incentive object.
     */
    public function build(): LoyaltyPromotionIncentive
    {
        return CoreHelper::clone($this->instance);
    }
}
