<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Describes a subscription plan variation. A subscription plan variation represents how the
 * subscription for a product or service is sold.
 * For more information, see [Subscription Plans and Variations](https://developer.squareup.
 * com/docs/subscriptions-api/plans-and-variations).
 */
class CatalogSubscriptionPlanVariation implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var SubscriptionPhase[]
     */
    private $phases;

    /**
     * @var array
     */
    private $subscriptionPlanId = [];

    /**
     * @var array
     */
    private $monthlyBillingAnchorDate = [];

    /**
     * @var array
     */
    private $canProrate = [];

    /**
     * @var array
     */
    private $successorPlanVariationId = [];

    /**
     * @param string $name
     * @param SubscriptionPhase[] $phases
     */
    public function __construct(string $name, array $phases)
    {
        $this->name = $name;
        $this->phases = $phases;
    }

    /**
     * Returns Name.
     * The name of the plan variation.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The name of the plan variation.
     *
     * @required
     * @maps name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns Phases.
     * A list containing each [SubscriptionPhase](entity:SubscriptionPhase) for this plan variation.
     *
     * @return SubscriptionPhase[]
     */
    public function getPhases(): array
    {
        return $this->phases;
    }

    /**
     * Sets Phases.
     * A list containing each [SubscriptionPhase](entity:SubscriptionPhase) for this plan variation.
     *
     * @required
     * @maps phases
     *
     * @param SubscriptionPhase[] $phases
     */
    public function setPhases(array $phases): void
    {
        $this->phases = $phases;
    }

    /**
     * Returns Subscription Plan Id.
     * The id of the subscription plan, if there is one.
     */
    public function getSubscriptionPlanId(): ?string
    {
        if (count($this->subscriptionPlanId) == 0) {
            return null;
        }
        return $this->subscriptionPlanId['value'];
    }

    /**
     * Sets Subscription Plan Id.
     * The id of the subscription plan, if there is one.
     *
     * @maps subscription_plan_id
     */
    public function setSubscriptionPlanId(?string $subscriptionPlanId): void
    {
        $this->subscriptionPlanId['value'] = $subscriptionPlanId;
    }

    /**
     * Unsets Subscription Plan Id.
     * The id of the subscription plan, if there is one.
     */
    public function unsetSubscriptionPlanId(): void
    {
        $this->subscriptionPlanId = [];
    }

    /**
     * Returns Monthly Billing Anchor Date.
     * The day of the month the billing period starts.
     */
    public function getMonthlyBillingAnchorDate(): ?int
    {
        if (count($this->monthlyBillingAnchorDate) == 0) {
            return null;
        }
        return $this->monthlyBillingAnchorDate['value'];
    }

    /**
     * Sets Monthly Billing Anchor Date.
     * The day of the month the billing period starts.
     *
     * @maps monthly_billing_anchor_date
     */
    public function setMonthlyBillingAnchorDate(?int $monthlyBillingAnchorDate): void
    {
        $this->monthlyBillingAnchorDate['value'] = $monthlyBillingAnchorDate;
    }

    /**
     * Unsets Monthly Billing Anchor Date.
     * The day of the month the billing period starts.
     */
    public function unsetMonthlyBillingAnchorDate(): void
    {
        $this->monthlyBillingAnchorDate = [];
    }

    /**
     * Returns Can Prorate.
     * Whether bills for this plan variation can be split for proration.
     */
    public function getCanProrate(): ?bool
    {
        if (count($this->canProrate) == 0) {
            return null;
        }
        return $this->canProrate['value'];
    }

    /**
     * Sets Can Prorate.
     * Whether bills for this plan variation can be split for proration.
     *
     * @maps can_prorate
     */
    public function setCanProrate(?bool $canProrate): void
    {
        $this->canProrate['value'] = $canProrate;
    }

    /**
     * Unsets Can Prorate.
     * Whether bills for this plan variation can be split for proration.
     */
    public function unsetCanProrate(): void
    {
        $this->canProrate = [];
    }

    /**
     * Returns Successor Plan Variation Id.
     * The ID of a "successor" plan variation to this one. If the field is set, and this object is disabled
     * at all
     * locations, it indicates that this variation is deprecated and the object identified by the successor
     * ID be used in
     * its stead.
     */
    public function getSuccessorPlanVariationId(): ?string
    {
        if (count($this->successorPlanVariationId) == 0) {
            return null;
        }
        return $this->successorPlanVariationId['value'];
    }

    /**
     * Sets Successor Plan Variation Id.
     * The ID of a "successor" plan variation to this one. If the field is set, and this object is disabled
     * at all
     * locations, it indicates that this variation is deprecated and the object identified by the successor
     * ID be used in
     * its stead.
     *
     * @maps successor_plan_variation_id
     */
    public function setSuccessorPlanVariationId(?string $successorPlanVariationId): void
    {
        $this->successorPlanVariationId['value'] = $successorPlanVariationId;
    }

    /**
     * Unsets Successor Plan Variation Id.
     * The ID of a "successor" plan variation to this one. If the field is set, and this object is disabled
     * at all
     * locations, it indicates that this variation is deprecated and the object identified by the successor
     * ID be used in
     * its stead.
     */
    public function unsetSuccessorPlanVariationId(): void
    {
        $this->successorPlanVariationId = [];
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
        $json['name']                            = $this->name;
        $json['phases']                          = $this->phases;
        if (!empty($this->subscriptionPlanId)) {
            $json['subscription_plan_id']        = $this->subscriptionPlanId['value'];
        }
        if (!empty($this->monthlyBillingAnchorDate)) {
            $json['monthly_billing_anchor_date'] = $this->monthlyBillingAnchorDate['value'];
        }
        if (!empty($this->canProrate)) {
            $json['can_prorate']                 = $this->canProrate['value'];
        }
        if (!empty($this->successorPlanVariationId)) {
            $json['successor_plan_variation_id'] = $this->successorPlanVariationId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
