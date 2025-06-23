<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Describes a subscription plan. A subscription plan represents what you want to sell in a
 * subscription model, and includes references to each of the associated subscription plan variations.
 * For more information, see [Subscription Plans and Variations](https://developer.squareup.
 * com/docs/subscriptions-api/plans-and-variations).
 */
class CatalogSubscriptionPlan implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $phases = [];

    /**
     * @var array
     */
    private $subscriptionPlanVariations = [];

    /**
     * @var array
     */
    private $eligibleItemIds = [];

    /**
     * @var array
     */
    private $eligibleCategoryIds = [];

    /**
     * @var array
     */
    private $allItems = [];

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns Name.
     * The name of the plan.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The name of the plan.
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
     * A list of SubscriptionPhase containing the [SubscriptionPhase](entity:SubscriptionPhase) for this
     * plan.
     * This field it required. Not including this field will throw a REQUIRED_FIELD_MISSING error
     *
     * @return SubscriptionPhase[]|null
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
     * A list of SubscriptionPhase containing the [SubscriptionPhase](entity:SubscriptionPhase) for this
     * plan.
     * This field it required. Not including this field will throw a REQUIRED_FIELD_MISSING error
     *
     * @maps phases
     *
     * @param SubscriptionPhase[]|null $phases
     */
    public function setPhases(?array $phases): void
    {
        $this->phases['value'] = $phases;
    }

    /**
     * Unsets Phases.
     * A list of SubscriptionPhase containing the [SubscriptionPhase](entity:SubscriptionPhase) for this
     * plan.
     * This field it required. Not including this field will throw a REQUIRED_FIELD_MISSING error
     */
    public function unsetPhases(): void
    {
        $this->phases = [];
    }

    /**
     * Returns Subscription Plan Variations.
     * The list of subscription plan variations available for this product
     *
     * @return CatalogObject[]|null
     */
    public function getSubscriptionPlanVariations(): ?array
    {
        if (count($this->subscriptionPlanVariations) == 0) {
            return null;
        }
        return $this->subscriptionPlanVariations['value'];
    }

    /**
     * Sets Subscription Plan Variations.
     * The list of subscription plan variations available for this product
     *
     * @maps subscription_plan_variations
     *
     * @param CatalogObject[]|null $subscriptionPlanVariations
     */
    public function setSubscriptionPlanVariations(?array $subscriptionPlanVariations): void
    {
        $this->subscriptionPlanVariations['value'] = $subscriptionPlanVariations;
    }

    /**
     * Unsets Subscription Plan Variations.
     * The list of subscription plan variations available for this product
     */
    public function unsetSubscriptionPlanVariations(): void
    {
        $this->subscriptionPlanVariations = [];
    }

    /**
     * Returns Eligible Item Ids.
     * The list of IDs of `CatalogItems` that are eligible for subscription by this SubscriptionPlan's
     * variations.
     *
     * @return string[]|null
     */
    public function getEligibleItemIds(): ?array
    {
        if (count($this->eligibleItemIds) == 0) {
            return null;
        }
        return $this->eligibleItemIds['value'];
    }

    /**
     * Sets Eligible Item Ids.
     * The list of IDs of `CatalogItems` that are eligible for subscription by this SubscriptionPlan's
     * variations.
     *
     * @maps eligible_item_ids
     *
     * @param string[]|null $eligibleItemIds
     */
    public function setEligibleItemIds(?array $eligibleItemIds): void
    {
        $this->eligibleItemIds['value'] = $eligibleItemIds;
    }

    /**
     * Unsets Eligible Item Ids.
     * The list of IDs of `CatalogItems` that are eligible for subscription by this SubscriptionPlan's
     * variations.
     */
    public function unsetEligibleItemIds(): void
    {
        $this->eligibleItemIds = [];
    }

    /**
     * Returns Eligible Category Ids.
     * The list of IDs of `CatalogCategory` that are eligible for subscription by this SubscriptionPlan's
     * variations.
     *
     * @return string[]|null
     */
    public function getEligibleCategoryIds(): ?array
    {
        if (count($this->eligibleCategoryIds) == 0) {
            return null;
        }
        return $this->eligibleCategoryIds['value'];
    }

    /**
     * Sets Eligible Category Ids.
     * The list of IDs of `CatalogCategory` that are eligible for subscription by this SubscriptionPlan's
     * variations.
     *
     * @maps eligible_category_ids
     *
     * @param string[]|null $eligibleCategoryIds
     */
    public function setEligibleCategoryIds(?array $eligibleCategoryIds): void
    {
        $this->eligibleCategoryIds['value'] = $eligibleCategoryIds;
    }

    /**
     * Unsets Eligible Category Ids.
     * The list of IDs of `CatalogCategory` that are eligible for subscription by this SubscriptionPlan's
     * variations.
     */
    public function unsetEligibleCategoryIds(): void
    {
        $this->eligibleCategoryIds = [];
    }

    /**
     * Returns All Items.
     * If true, all items in the merchant's catalog are subscribable by this SubscriptionPlan.
     */
    public function getAllItems(): ?bool
    {
        if (count($this->allItems) == 0) {
            return null;
        }
        return $this->allItems['value'];
    }

    /**
     * Sets All Items.
     * If true, all items in the merchant's catalog are subscribable by this SubscriptionPlan.
     *
     * @maps all_items
     */
    public function setAllItems(?bool $allItems): void
    {
        $this->allItems['value'] = $allItems;
    }

    /**
     * Unsets All Items.
     * If true, all items in the merchant's catalog are subscribable by this SubscriptionPlan.
     */
    public function unsetAllItems(): void
    {
        $this->allItems = [];
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
        $json['name']                             = $this->name;
        if (!empty($this->phases)) {
            $json['phases']                       = $this->phases['value'];
        }
        if (!empty($this->subscriptionPlanVariations)) {
            $json['subscription_plan_variations'] = $this->subscriptionPlanVariations['value'];
        }
        if (!empty($this->eligibleItemIds)) {
            $json['eligible_item_ids']            = $this->eligibleItemIds['value'];
        }
        if (!empty($this->eligibleCategoryIds)) {
            $json['eligible_category_ids']        = $this->eligibleCategoryIds['value'];
        }
        if (!empty($this->allItems)) {
            $json['all_items']                    = $this->allItems['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
