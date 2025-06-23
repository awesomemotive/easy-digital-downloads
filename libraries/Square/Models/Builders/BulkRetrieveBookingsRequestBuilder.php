<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkRetrieveBookingsRequest;

/**
 * Builder for model BulkRetrieveBookingsRequest
 *
 * @see BulkRetrieveBookingsRequest
 */
class BulkRetrieveBookingsRequestBuilder
{
    /**
     * @var BulkRetrieveBookingsRequest
     */
    private $instance;

    private function __construct(BulkRetrieveBookingsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Retrieve Bookings Request Builder object.
     *
     * @param string[] $bookingIds
     */
    public static function init(array $bookingIds): self
    {
        return new self(new BulkRetrieveBookingsRequest($bookingIds));
    }

    /**
     * Initializes a new Bulk Retrieve Bookings Request object.
     */
    public function build(): BulkRetrieveBookingsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
