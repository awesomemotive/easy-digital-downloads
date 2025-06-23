<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class ListLocationBookingProfilesResponse implements \JsonSerializable
{
    /**
     * @var LocationBookingProfile[]|null
     */
    private $locationBookingProfiles;

    /**
     * @var string|null
     */
    private $cursor;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Location Booking Profiles.
     * The list of a seller's location booking profiles.
     *
     * @return LocationBookingProfile[]|null
     */
    public function getLocationBookingProfiles(): ?array
    {
        return $this->locationBookingProfiles;
    }

    /**
     * Sets Location Booking Profiles.
     * The list of a seller's location booking profiles.
     *
     * @maps location_booking_profiles
     *
     * @param LocationBookingProfile[]|null $locationBookingProfiles
     */
    public function setLocationBookingProfiles(?array $locationBookingProfiles): void
    {
        $this->locationBookingProfiles = $locationBookingProfiles;
    }

    /**
     * Returns Cursor.
     * The pagination cursor to be used in the subsequent request to get the next page of the results. Stop
     * retrieving the next page of the results when the cursor is not set.
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Sets Cursor.
     * The pagination cursor to be used in the subsequent request to get the next page of the results. Stop
     * retrieving the next page of the results when the cursor is not set.
     *
     * @maps cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
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
        if (isset($this->locationBookingProfiles)) {
            $json['location_booking_profiles'] = $this->locationBookingProfiles;
        }
        if (isset($this->cursor)) {
            $json['cursor']                    = $this->cursor;
        }
        if (isset($this->errors)) {
            $json['errors']                    = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
