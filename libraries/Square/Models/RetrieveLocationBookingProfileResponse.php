<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class RetrieveLocationBookingProfileResponse implements \JsonSerializable
{
    /**
     * @var LocationBookingProfile|null
     */
    private $locationBookingProfile;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Location Booking Profile.
     * The booking profile of a seller's location, including the location's ID and whether the location is
     * enabled for online booking.
     */
    public function getLocationBookingProfile(): ?LocationBookingProfile
    {
        return $this->locationBookingProfile;
    }

    /**
     * Sets Location Booking Profile.
     * The booking profile of a seller's location, including the location's ID and whether the location is
     * enabled for online booking.
     *
     * @maps location_booking_profile
     */
    public function setLocationBookingProfile(?LocationBookingProfile $locationBookingProfile): void
    {
        $this->locationBookingProfile = $locationBookingProfile;
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
        if (isset($this->locationBookingProfile)) {
            $json['location_booking_profile'] = $this->locationBookingProfile;
        }
        if (isset($this->errors)) {
            $json['errors']                   = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
