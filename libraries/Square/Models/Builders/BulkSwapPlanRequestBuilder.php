<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkSwapPlanRequest;

/**
 * Builder for model BulkSwapPlanRequest
 *
 * @see BulkSwapPlanRequest
 */
class BulkSwapPlanRequestBuilder
{
    /**
     * @var BulkSwapPlanRequest
     */
    private $instance;

    private function __construct(BulkSwapPlanRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Swap Plan Request Builder object.
     *
     * @param string $newPlanVariationId
     * @param string $oldPlanVariationId
     * @param string $locationId
     */
    public static function init(string $newPlanVariationId, string $oldPlanVariationId, string $locationId): self
    {
        return new self(new BulkSwapPlanRequest($newPlanVariationId, $oldPlanVariationId, $locationId));
    }

    /**
     * Initializes a new Bulk Swap Plan Request object.
     */
    public function build(): BulkSwapPlanRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
