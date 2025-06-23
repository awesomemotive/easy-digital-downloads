<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Phase;
use EDD\Vendor\Square\Models\SubscriptionEvent;
use EDD\Vendor\Square\Models\SubscriptionEventInfo;

/**
 * Builder for model SubscriptionEvent
 *
 * @see SubscriptionEvent
 */
class SubscriptionEventBuilder
{
    /**
     * @var SubscriptionEvent
     */
    private $instance;

    private function __construct(SubscriptionEvent $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Subscription Event Builder object.
     *
     * @param string $id
     * @param string $subscriptionEventType
     * @param string $effectiveDate
     * @param string $planVariationId
     */
    public static function init(
        string $id,
        string $subscriptionEventType,
        string $effectiveDate,
        string $planVariationId
    ): self {
        return new self(new SubscriptionEvent($id, $subscriptionEventType, $effectiveDate, $planVariationId));
    }

    /**
     * Sets monthly billing anchor date field.
     *
     * @param int|null $value
     */
    public function monthlyBillingAnchorDate(?int $value): self
    {
        $this->instance->setMonthlyBillingAnchorDate($value);
        return $this;
    }

    /**
     * Sets info field.
     *
     * @param SubscriptionEventInfo|null $value
     */
    public function info(?SubscriptionEventInfo $value): self
    {
        $this->instance->setInfo($value);
        return $this;
    }

    /**
     * Sets phases field.
     *
     * @param Phase[]|null $value
     */
    public function phases(?array $value): self
    {
        $this->instance->setPhases($value);
        return $this;
    }

    /**
     * Unsets phases field.
     */
    public function unsetPhases(): self
    {
        $this->instance->unsetPhases();
        return $this;
    }

    /**
     * Initializes a new Subscription Event object.
     */
    public function build(): SubscriptionEvent
    {
        return CoreHelper::clone($this->instance);
    }
}
