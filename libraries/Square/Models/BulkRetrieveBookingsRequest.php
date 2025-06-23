<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Request payload for bulk retrieval of bookings.
 */
class BulkRetrieveBookingsRequest implements \JsonSerializable
{
    /**
     * @var string[]
     */
    private $bookingIds;

    /**
     * @param string[] $bookingIds
     */
    public function __construct(array $bookingIds)
    {
        $this->bookingIds = $bookingIds;
    }

    /**
     * Returns Booking Ids.
     * A non-empty list of [Booking](entity:Booking) IDs specifying bookings to retrieve.
     *
     * @return string[]
     */
    public function getBookingIds(): array
    {
        return $this->bookingIds;
    }

    /**
     * Sets Booking Ids.
     * A non-empty list of [Booking](entity:Booking) IDs specifying bookings to retrieve.
     *
     * @required
     * @maps booking_ids
     *
     * @param string[] $bookingIds
     */
    public function setBookingIds(array $bookingIds): void
    {
        $this->bookingIds = $bookingIds;
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
        $json['booking_ids'] = $this->bookingIds;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
