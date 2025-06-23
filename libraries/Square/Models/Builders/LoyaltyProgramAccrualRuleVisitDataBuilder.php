<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyProgramAccrualRuleVisitData;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model LoyaltyProgramAccrualRuleVisitData
 *
 * @see LoyaltyProgramAccrualRuleVisitData
 */
class LoyaltyProgramAccrualRuleVisitDataBuilder
{
    /**
     * @var LoyaltyProgramAccrualRuleVisitData
     */
    private $instance;

    private function __construct(LoyaltyProgramAccrualRuleVisitData $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Program Accrual Rule Visit Data Builder object.
     *
     * @param string $taxMode
     */
    public static function init(string $taxMode): self
    {
        return new self(new LoyaltyProgramAccrualRuleVisitData($taxMode));
    }

    /**
     * Sets minimum amount money field.
     *
     * @param Money|null $value
     */
    public function minimumAmountMoney(?Money $value): self
    {
        $this->instance->setMinimumAmountMoney($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Program Accrual Rule Visit Data object.
     */
    public function build(): LoyaltyProgramAccrualRuleVisitData
    {
        return CoreHelper::clone($this->instance);
    }
}
