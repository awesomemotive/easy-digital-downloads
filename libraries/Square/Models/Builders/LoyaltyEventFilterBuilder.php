<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventDateTimeFilter;
use EDD\Vendor\Square\Models\LoyaltyEventFilter;
use EDD\Vendor\Square\Models\LoyaltyEventLocationFilter;
use EDD\Vendor\Square\Models\LoyaltyEventLoyaltyAccountFilter;
use EDD\Vendor\Square\Models\LoyaltyEventOrderFilter;
use EDD\Vendor\Square\Models\LoyaltyEventTypeFilter;

/**
 * Builder for model LoyaltyEventFilter
 *
 * @see LoyaltyEventFilter
 */
class LoyaltyEventFilterBuilder
{
    /**
     * @var LoyaltyEventFilter
     */
    private $instance;

    private function __construct(LoyaltyEventFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new LoyaltyEventFilter());
    }

    /**
     * Sets loyalty account filter field.
     *
     * @param LoyaltyEventLoyaltyAccountFilter|null $value
     */
    public function loyaltyAccountFilter(?LoyaltyEventLoyaltyAccountFilter $value): self
    {
        $this->instance->setLoyaltyAccountFilter($value);
        return $this;
    }

    /**
     * Sets type filter field.
     *
     * @param LoyaltyEventTypeFilter|null $value
     */
    public function typeFilter(?LoyaltyEventTypeFilter $value): self
    {
        $this->instance->setTypeFilter($value);
        return $this;
    }

    /**
     * Sets date time filter field.
     *
     * @param LoyaltyEventDateTimeFilter|null $value
     */
    public function dateTimeFilter(?LoyaltyEventDateTimeFilter $value): self
    {
        $this->instance->setDateTimeFilter($value);
        return $this;
    }

    /**
     * Sets location filter field.
     *
     * @param LoyaltyEventLocationFilter|null $value
     */
    public function locationFilter(?LoyaltyEventLocationFilter $value): self
    {
        $this->instance->setLocationFilter($value);
        return $this;
    }

    /**
     * Sets order filter field.
     *
     * @param LoyaltyEventOrderFilter|null $value
     */
    public function orderFilter(?LoyaltyEventOrderFilter $value): self
    {
        $this->instance->setOrderFilter($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Event Filter object.
     */
    public function build(): LoyaltyEventFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
