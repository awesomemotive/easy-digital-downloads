<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents the metadata for a `POINTS_MULTIPLIER` type of [loyalty promotion
 * incentive]($m/LoyaltyPromotionIncentive).
 */
class LoyaltyPromotionIncentivePointsMultiplierData implements \JsonSerializable
{
    /**
     * @var array
     */
    private $pointsMultiplier = [];

    /**
     * @var array
     */
    private $multiplier = [];

    /**
     * Returns Points Multiplier.
     * The multiplier used to calculate the number of points earned each time the promotion
     * is triggered. For example, suppose a purchase qualifies for 5 points from the base loyalty program.
     * If the purchase also qualifies for a `POINTS_MULTIPLIER` promotion incentive with a
     * `points_multiplier`
     * of 3, the buyer earns a total of 15 points (5 program points x 3 promotion multiplier = 15 points).
     *
     * DEPRECATED at version 2023-08-16. Replaced by the `multiplier` field.
     *
     * One of the following is required when specifying a points multiplier:
     * - (Recommended) The `multiplier` field.
     * - This deprecated `points_multiplier` field. If provided in the request, EDD\Vendor\Square also returns
     * `multiplier`
     * with the equivalent value.
     */
    public function getPointsMultiplier(): ?int
    {
        if (count($this->pointsMultiplier) == 0) {
            return null;
        }
        return $this->pointsMultiplier['value'];
    }

    /**
     * Sets Points Multiplier.
     * The multiplier used to calculate the number of points earned each time the promotion
     * is triggered. For example, suppose a purchase qualifies for 5 points from the base loyalty program.
     * If the purchase also qualifies for a `POINTS_MULTIPLIER` promotion incentive with a
     * `points_multiplier`
     * of 3, the buyer earns a total of 15 points (5 program points x 3 promotion multiplier = 15 points).
     *
     * DEPRECATED at version 2023-08-16. Replaced by the `multiplier` field.
     *
     * One of the following is required when specifying a points multiplier:
     * - (Recommended) The `multiplier` field.
     * - This deprecated `points_multiplier` field. If provided in the request, EDD\Vendor\Square also returns
     * `multiplier`
     * with the equivalent value.
     *
     * @maps points_multiplier
     */
    public function setPointsMultiplier(?int $pointsMultiplier): void
    {
        $this->pointsMultiplier['value'] = $pointsMultiplier;
    }

    /**
     * Unsets Points Multiplier.
     * The multiplier used to calculate the number of points earned each time the promotion
     * is triggered. For example, suppose a purchase qualifies for 5 points from the base loyalty program.
     * If the purchase also qualifies for a `POINTS_MULTIPLIER` promotion incentive with a
     * `points_multiplier`
     * of 3, the buyer earns a total of 15 points (5 program points x 3 promotion multiplier = 15 points).
     *
     * DEPRECATED at version 2023-08-16. Replaced by the `multiplier` field.
     *
     * One of the following is required when specifying a points multiplier:
     * - (Recommended) The `multiplier` field.
     * - This deprecated `points_multiplier` field. If provided in the request, EDD\Vendor\Square also returns
     * `multiplier`
     * with the equivalent value.
     */
    public function unsetPointsMultiplier(): void
    {
        $this->pointsMultiplier = [];
    }

    /**
     * Returns Multiplier.
     * The multiplier used to calculate the number of points earned each time the promotion is triggered,
     * specified as a string representation of a decimal. EDD\Vendor\Square supports multipliers up to 10x, with
     * three
     * point precision for decimal multipliers. For example, suppose a purchase qualifies for 4 points from
     * the
     * base loyalty program. If the purchase also qualifies for a `POINTS_MULTIPLIER` promotion incentive
     * with a
     * `multiplier` of "1.5", the buyer earns a total of 6 points (4 program points x 1.5 promotion
     * multiplier = 6 points).
     * Fractional points are dropped.
     *
     * One of the following is required when specifying a points multiplier:
     * - (Recommended) This `multiplier` field.
     * - The deprecated `points_multiplier` field. If provided in the request, EDD\Vendor\Square also returns
     * `multiplier`
     * with the equivalent value.
     */
    public function getMultiplier(): ?string
    {
        if (count($this->multiplier) == 0) {
            return null;
        }
        return $this->multiplier['value'];
    }

    /**
     * Sets Multiplier.
     * The multiplier used to calculate the number of points earned each time the promotion is triggered,
     * specified as a string representation of a decimal. EDD\Vendor\Square supports multipliers up to 10x, with
     * three
     * point precision for decimal multipliers. For example, suppose a purchase qualifies for 4 points from
     * the
     * base loyalty program. If the purchase also qualifies for a `POINTS_MULTIPLIER` promotion incentive
     * with a
     * `multiplier` of "1.5", the buyer earns a total of 6 points (4 program points x 1.5 promotion
     * multiplier = 6 points).
     * Fractional points are dropped.
     *
     * One of the following is required when specifying a points multiplier:
     * - (Recommended) This `multiplier` field.
     * - The deprecated `points_multiplier` field. If provided in the request, EDD\Vendor\Square also returns
     * `multiplier`
     * with the equivalent value.
     *
     * @maps multiplier
     */
    public function setMultiplier(?string $multiplier): void
    {
        $this->multiplier['value'] = $multiplier;
    }

    /**
     * Unsets Multiplier.
     * The multiplier used to calculate the number of points earned each time the promotion is triggered,
     * specified as a string representation of a decimal. EDD\Vendor\Square supports multipliers up to 10x, with
     * three
     * point precision for decimal multipliers. For example, suppose a purchase qualifies for 4 points from
     * the
     * base loyalty program. If the purchase also qualifies for a `POINTS_MULTIPLIER` promotion incentive
     * with a
     * `multiplier` of "1.5", the buyer earns a total of 6 points (4 program points x 1.5 promotion
     * multiplier = 6 points).
     * Fractional points are dropped.
     *
     * One of the following is required when specifying a points multiplier:
     * - (Recommended) This `multiplier` field.
     * - The deprecated `points_multiplier` field. If provided in the request, EDD\Vendor\Square also returns
     * `multiplier`
     * with the equivalent value.
     */
    public function unsetMultiplier(): void
    {
        $this->multiplier = [];
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
        if (!empty($this->pointsMultiplier)) {
            $json['points_multiplier'] = $this->pointsMultiplier['value'];
        }
        if (!empty($this->multiplier)) {
            $json['multiplier']        = $this->multiplier['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
