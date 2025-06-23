<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRule;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRuleCategoryData;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRuleItemVariationData;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRuleSpendData;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRuleVisitData;

/**
 * Builder for model LoyaltyProgramAccrualRule
 *
 * @see LoyaltyProgramAccrualRule
 */
class LoyaltyProgramAccrualRuleBuilder
{
    /**
     * @var LoyaltyProgramAccrualRule
     */
    private $instance;

    private function __construct(LoyaltyProgramAccrualRule $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Program Accrual Rule Builder object.
     *
     * @param string $accrualType
     */
    public static function init(string $accrualType): self
    {
        return new self(new LoyaltyProgramAccrualRule($accrualType));
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
     * Unsets points field.
     */
    public function unsetPoints(): self
    {
        $this->instance->unsetPoints();
        return $this;
    }

    /**
     * Sets visit data field.
     *
     * @param LoyaltyProgramAccrualRuleVisitData|null $value
     */
    public function visitData(?LoyaltyProgramAccrualRuleVisitData $value): self
    {
        $this->instance->setVisitData($value);
        return $this;
    }

    /**
     * Sets spend data field.
     *
     * @param LoyaltyProgramAccrualRuleSpendData|null $value
     */
    public function spendData(?LoyaltyProgramAccrualRuleSpendData $value): self
    {
        $this->instance->setSpendData($value);
        return $this;
    }

    /**
     * Sets item variation data field.
     *
     * @param LoyaltyProgramAccrualRuleItemVariationData|null $value
     */
    public function itemVariationData(?LoyaltyProgramAccrualRuleItemVariationData $value): self
    {
        $this->instance->setItemVariationData($value);
        return $this;
    }

    /**
     * Sets category data field.
     *
     * @param LoyaltyProgramAccrualRuleCategoryData|null $value
     */
    public function categoryData(?LoyaltyProgramAccrualRuleCategoryData $value): self
    {
        $this->instance->setCategoryData($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Program Accrual Rule object.
     */
    public function build(): LoyaltyProgramAccrualRule
    {
        return CoreHelper::clone($this->instance);
    }
}
