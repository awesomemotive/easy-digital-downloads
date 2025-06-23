<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Describes changes to a subscription and the subscription status.
 */
class SubscriptionEvent implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $subscriptionEventType;

    /**
     * @var string
     */
    private $effectiveDate;

    /**
     * @var int|null
     */
    private $monthlyBillingAnchorDate;

    /**
     * @var SubscriptionEventInfo|null
     */
    private $info;

    /**
     * @var array
     */
    private $phases = [];

    /**
     * @var string
     */
    private $planVariationId;

    /**
     * @param string $id
     * @param string $subscriptionEventType
     * @param string $effectiveDate
     * @param string $planVariationId
     */
    public function __construct(
        string $id,
        string $subscriptionEventType,
        string $effectiveDate,
        string $planVariationId
    ) {
        $this->id = $id;
        $this->subscriptionEventType = $subscriptionEventType;
        $this->effectiveDate = $effectiveDate;
        $this->planVariationId = $planVariationId;
    }

    /**
     * Returns Id.
     * The ID of the subscription event.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The ID of the subscription event.
     *
     * @required
     * @maps id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Subscription Event Type.
     * Supported types of an event occurred to a subscription.
     */
    public function getSubscriptionEventType(): string
    {
        return $this->subscriptionEventType;
    }

    /**
     * Sets Subscription Event Type.
     * Supported types of an event occurred to a subscription.
     *
     * @required
     * @maps subscription_event_type
     */
    public function setSubscriptionEventType(string $subscriptionEventType): void
    {
        $this->subscriptionEventType = $subscriptionEventType;
    }

    /**
     * Returns Effective Date.
     * The `YYYY-MM-DD`-formatted date (for example, 2013-01-15) when the subscription event occurred.
     */
    public function getEffectiveDate(): string
    {
        return $this->effectiveDate;
    }

    /**
     * Sets Effective Date.
     * The `YYYY-MM-DD`-formatted date (for example, 2013-01-15) when the subscription event occurred.
     *
     * @required
     * @maps effective_date
     */
    public function setEffectiveDate(string $effectiveDate): void
    {
        $this->effectiveDate = $effectiveDate;
    }

    /**
     * Returns Monthly Billing Anchor Date.
     * The day-of-the-month the billing anchor date was changed to, if applicable.
     */
    public function getMonthlyBillingAnchorDate(): ?int
    {
        return $this->monthlyBillingAnchorDate;
    }

    /**
     * Sets Monthly Billing Anchor Date.
     * The day-of-the-month the billing anchor date was changed to, if applicable.
     *
     * @maps monthly_billing_anchor_date
     */
    public function setMonthlyBillingAnchorDate(?int $monthlyBillingAnchorDate): void
    {
        $this->monthlyBillingAnchorDate = $monthlyBillingAnchorDate;
    }

    /**
     * Returns Info.
     * Provides information about the subscription event.
     */
    public function getInfo(): ?SubscriptionEventInfo
    {
        return $this->info;
    }

    /**
     * Sets Info.
     * Provides information about the subscription event.
     *
     * @maps info
     */
    public function setInfo(?SubscriptionEventInfo $info): void
    {
        $this->info = $info;
    }

    /**
     * Returns Phases.
     * A list of Phases, to pass phase-specific information used in the swap.
     *
     * @return Phase[]|null
     */
    public function getPhases(): ?array
    {
        if (count($this->phases) == 0) {
            return null;
        }
        return $this->phases['value'];
    }

    /**
     * Sets Phases.
     * A list of Phases, to pass phase-specific information used in the swap.
     *
     * @maps phases
     *
     * @param Phase[]|null $phases
     */
    public function setPhases(?array $phases): void
    {
        $this->phases['value'] = $phases;
    }

    /**
     * Unsets Phases.
     * A list of Phases, to pass phase-specific information used in the swap.
     */
    public function unsetPhases(): void
    {
        $this->phases = [];
    }

    /**
     * Returns Plan Variation Id.
     * The ID of the subscription plan variation associated with the subscription.
     */
    public function getPlanVariationId(): string
    {
        return $this->planVariationId;
    }

    /**
     * Sets Plan Variation Id.
     * The ID of the subscription plan variation associated with the subscription.
     *
     * @required
     * @maps plan_variation_id
     */
    public function setPlanVariationId(string $planVariationId): void
    {
        $this->planVariationId = $planVariationId;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        $json['id']                              = $this->id;
        $json['subscription_event_type']         = $this->subscriptionEventType;
        $json['effective_date']                  = $this->effectiveDate;
        if (isset($this->monthlyBillingAnchorDate)) {
            $json['monthly_billing_anchor_date'] = $this->monthlyBillingAnchorDate;
        }
        if (isset($this->info)) {
            $json['info']                        = $this->info;
        }
        if (!empty($this->phases)) {
            $json['phases']                      = $this->phases['value'];
        }
        $json['plan_variation_id']               = $this->planVariationId;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
