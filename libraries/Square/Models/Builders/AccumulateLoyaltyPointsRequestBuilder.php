<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AccumulateLoyaltyPointsRequest;
use EDD\Vendor\Square\Models\LoyaltyEventAccumulatePoints;

/**
 * Builder for model AccumulateLoyaltyPointsRequest
 *
 * @see AccumulateLoyaltyPointsRequest
 */
class AccumulateLoyaltyPointsRequestBuilder
{
    /**
     * @var AccumulateLoyaltyPointsRequest
     */
    private $instance;

    private function __construct(AccumulateLoyaltyPointsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Accumulate Loyalty Points Request Builder object.
     *
     * @param LoyaltyEventAccumulatePoints $accumulatePoints
     * @param string $idempotencyKey
     * @param string $locationId
     */
    public static function init(
        LoyaltyEventAccumulatePoints $accumulatePoints,
        string $idempotencyKey,
        string $locationId
    ): self {
        return new self(new AccumulateLoyaltyPointsRequest($accumulatePoints, $idempotencyKey, $locationId));
    }

    /**
     * Initializes a new Accumulate Loyalty Points Request object.
     */
    public function build(): AccumulateLoyaltyPointsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
