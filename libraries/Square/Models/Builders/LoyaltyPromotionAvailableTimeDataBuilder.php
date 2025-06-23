<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyPromotionAvailableTimeData;

/**
 * Builder for model LoyaltyPromotionAvailableTimeData
 *
 * @see LoyaltyPromotionAvailableTimeData
 */
class LoyaltyPromotionAvailableTimeDataBuilder
{
    /**
     * @var LoyaltyPromotionAvailableTimeData
     */
    private $instance;

    private function __construct(LoyaltyPromotionAvailableTimeData $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Promotion Available Time Data Builder object.
     *
     * @param string[] $timePeriods
     */
    public static function init(array $timePeriods): self
    {
        return new self(new LoyaltyPromotionAvailableTimeData($timePeriods));
    }

    /**
     * Sets start date field.
     *
     * @param string|null $value
     */
    public function startDate(?string $value): self
    {
        $this->instance->setStartDate($value);
        return $this;
    }

    /**
     * Sets end date field.
     *
     * @param string|null $value
     */
    public function endDate(?string $value): self
    {
        $this->instance->setEndDate($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Promotion Available Time Data object.
     */
    public function build(): LoyaltyPromotionAvailableTimeData
    {
        return CoreHelper::clone($this->instance);
    }
}
