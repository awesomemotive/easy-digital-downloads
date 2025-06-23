<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines input parameters in a call to the
 * [BulkSwapPlan]($e/Subscriptions/BulkSwapPlan) endpoint.
 */
class BulkSwapPlanRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    private $newPlanVariationId;

    /**
     * @var string
     */
    private $oldPlanVariationId;

    /**
     * @var string
     */
    private $locationId;

    /**
     * @param string $newPlanVariationId
     * @param string $oldPlanVariationId
     * @param string $locationId
     */
    public function __construct(string $newPlanVariationId, string $oldPlanVariationId, string $locationId)
    {
        $this->newPlanVariationId = $newPlanVariationId;
        $this->oldPlanVariationId = $oldPlanVariationId;
        $this->locationId = $locationId;
    }

    /**
     * Returns New Plan Variation Id.
     * The ID of the new subscription plan variation.
     *
     * This field is required.
     */
    public function getNewPlanVariationId(): string
    {
        return $this->newPlanVariationId;
    }

    /**
     * Sets New Plan Variation Id.
     * The ID of the new subscription plan variation.
     *
     * This field is required.
     *
     * @required
     * @maps new_plan_variation_id
     */
    public function setNewPlanVariationId(string $newPlanVariationId): void
    {
        $this->newPlanVariationId = $newPlanVariationId;
    }

    /**
     * Returns Old Plan Variation Id.
     * The ID of the plan variation whose subscriptions should be swapped. Active subscriptions
     * using this plan variation will be subscribed to the new plan variation on their next billing
     * day.
     */
    public function getOldPlanVariationId(): string
    {
        return $this->oldPlanVariationId;
    }

    /**
     * Sets Old Plan Variation Id.
     * The ID of the plan variation whose subscriptions should be swapped. Active subscriptions
     * using this plan variation will be subscribed to the new plan variation on their next billing
     * day.
     *
     * @required
     * @maps old_plan_variation_id
     */
    public function setOldPlanVariationId(string $oldPlanVariationId): void
    {
        $this->oldPlanVariationId = $oldPlanVariationId;
    }

    /**
     * Returns Location Id.
     * The ID of the location to associate with the swapped subscriptions.
     */
    public function getLocationId(): string
    {
        return $this->locationId;
    }

    /**
     * Sets Location Id.
     * The ID of the location to associate with the swapped subscriptions.
     *
     * @required
     * @maps location_id
     */
    public function setLocationId(string $locationId): void
    {
        $this->locationId = $locationId;
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
        $json['new_plan_variation_id'] = $this->newPlanVariationId;
        $json['old_plan_variation_id'] = $this->oldPlanVariationId;
        $json['location_id']           = $this->locationId;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
