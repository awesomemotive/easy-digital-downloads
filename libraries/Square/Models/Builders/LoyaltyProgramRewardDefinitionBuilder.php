<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyProgramRewardDefinition;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model LoyaltyProgramRewardDefinition
 *
 * @see LoyaltyProgramRewardDefinition
 */
class LoyaltyProgramRewardDefinitionBuilder
{
    /**
     * @var LoyaltyProgramRewardDefinition
     */
    private $instance;

    private function __construct(LoyaltyProgramRewardDefinition $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Program Reward Definition Builder object.
     *
     * @param string $scope
     * @param string $discountType
     */
    public static function init(string $scope, string $discountType): self
    {
        return new self(new LoyaltyProgramRewardDefinition($scope, $discountType));
    }

    /**
     * Sets percentage discount field.
     *
     * @param string|null $value
     */
    public function percentageDiscount(?string $value): self
    {
        $this->instance->setPercentageDiscount($value);
        return $this;
    }

    /**
     * Sets catalog object ids field.
     *
     * @param string[]|null $value
     */
    public function catalogObjectIds(?array $value): self
    {
        $this->instance->setCatalogObjectIds($value);
        return $this;
    }

    /**
     * Sets fixed discount money field.
     *
     * @param Money|null $value
     */
    public function fixedDiscountMoney(?Money $value): self
    {
        $this->instance->setFixedDiscountMoney($value);
        return $this;
    }

    /**
     * Sets max discount money field.
     *
     * @param Money|null $value
     */
    public function maxDiscountMoney(?Money $value): self
    {
        $this->instance->setMaxDiscountMoney($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Program Reward Definition object.
     */
    public function build(): LoyaltyProgramRewardDefinition
    {
        return CoreHelper::clone($this->instance);
    }
}
