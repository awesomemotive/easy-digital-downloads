<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\SubscriptionPhase;
use EDD\Vendor\Square\Models\SubscriptionPricing;

/**
 * Builder for model SubscriptionPhase
 *
 * @see SubscriptionPhase
 */
class SubscriptionPhaseBuilder
{
    /**
     * @var SubscriptionPhase
     */
    private $instance;

    private function __construct(SubscriptionPhase $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Subscription Phase Builder object.
     *
     * @param string $cadence
     */
    public static function init(string $cadence): self
    {
        return new self(new SubscriptionPhase($cadence));
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
        return $this;
    }

    /**
     * Sets periods field.
     *
     * @param int|null $value
     */
    public function periods(?int $value): self
    {
        $this->instance->setPeriods($value);
        return $this;
    }

    /**
     * Unsets periods field.
     */
    public function unsetPeriods(): self
    {
        $this->instance->unsetPeriods();
        return $this;
    }

    /**
     * Sets recurring price money field.
     *
     * @param Money|null $value
     */
    public function recurringPriceMoney(?Money $value): self
    {
        $this->instance->setRecurringPriceMoney($value);
        return $this;
    }

    /**
     * Sets ordinal field.
     *
     * @param int|null $value
     */
    public function ordinal(?int $value): self
    {
        $this->instance->setOrdinal($value);
        return $this;
    }

    /**
     * Unsets ordinal field.
     */
    public function unsetOrdinal(): self
    {
        $this->instance->unsetOrdinal();
        return $this;
    }

    /**
     * Sets pricing field.
     *
     * @param SubscriptionPricing|null $value
     */
    public function pricing(?SubscriptionPricing $value): self
    {
        $this->instance->setPricing($value);
        return $this;
    }

    /**
     * Initializes a new Subscription Phase object.
     */
    public function build(): SubscriptionPhase
    {
        return CoreHelper::clone($this->instance);
    }
}
