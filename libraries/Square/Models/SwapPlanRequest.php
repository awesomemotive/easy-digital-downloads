<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines input parameters in a call to the
 * [SwapPlan]($e/Subscriptions/SwapPlan) endpoint.
 */
class SwapPlanRequest implements \JsonSerializable
{
    /**
     * @var array
     */
    private $newPlanVariationId = [];

    /**
     * @var array
     */
    private $phases = [];

    /**
     * Returns New Plan Variation Id.
     * The ID of the new subscription plan variation.
     *
     * This field is required.
     */
    public function getNewPlanVariationId(): ?string
    {
        if (count($this->newPlanVariationId) == 0) {
            return null;
        }
        return $this->newPlanVariationId['value'];
    }

    /**
     * Sets New Plan Variation Id.
     * The ID of the new subscription plan variation.
     *
     * This field is required.
     *
     * @maps new_plan_variation_id
     */
    public function setNewPlanVariationId(?string $newPlanVariationId): void
    {
        $this->newPlanVariationId['value'] = $newPlanVariationId;
    }

    /**
     * Unsets New Plan Variation Id.
     * The ID of the new subscription plan variation.
     *
     * This field is required.
     */
    public function unsetNewPlanVariationId(): void
    {
        $this->newPlanVariationId = [];
    }

    /**
     * Returns Phases.
     * A list of PhaseInputs, to pass phase-specific information used in the swap.
     *
     * @return PhaseInput[]|null
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
     * A list of PhaseInputs, to pass phase-specific information used in the swap.
     *
     * @maps phases
     *
     * @param PhaseInput[]|null $phases
     */
    public function setPhases(?array $phases): void
    {
        $this->phases['value'] = $phases;
    }

    /**
     * Unsets Phases.
     * A list of PhaseInputs, to pass phase-specific information used in the swap.
     */
    public function unsetPhases(): void
    {
        $this->phases = [];
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
        if (!empty($this->newPlanVariationId)) {
            $json['new_plan_variation_id'] = $this->newPlanVariationId['value'];
        }
        if (!empty($this->phases)) {
            $json['phases']                = $this->phases['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
