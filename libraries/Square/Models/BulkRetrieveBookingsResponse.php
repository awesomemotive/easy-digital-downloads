<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Response payload for bulk retrieval of bookings.
 */
class BulkRetrieveBookingsResponse implements \JsonSerializable
{
    /**
     * @var array<string,RetrieveBookingResponse>|null
     */
    private $bookings;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Bookings.
     * Requested bookings returned as a map containing `booking_id` as the key and
     * `RetrieveBookingResponse` as the value.
     *
     * @return array<string,RetrieveBookingResponse>|null
     */
    public function getBookings(): ?array
    {
        return $this->bookings;
    }

    /**
     * Sets Bookings.
     * Requested bookings returned as a map containing `booking_id` as the key and
     * `RetrieveBookingResponse` as the value.
     *
     * @maps bookings
     *
     * @param array<string,RetrieveBookingResponse>|null $bookings
     */
    public function setBookings(?array $bookings): void
    {
        $this->bookings = $bookings;
    }

    /**
     * Returns Errors.
     * Errors that occurred during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Errors that occurred during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
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
        if (isset($this->bookings)) {
            $json['bookings'] = $this->bookings;
        }
        if (isset($this->errors)) {
            $json['errors']   = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
