<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Response payload for the
 * [BulkRetrieveTeamMemberBookingProfiles]($e/Bookings/BulkRetrieveTeamMemberBookingProfiles) endpoint.
 */
class BulkRetrieveTeamMemberBookingProfilesResponse implements \JsonSerializable
{
    /**
     * @var array<string,RetrieveTeamMemberBookingProfileResponse>|null
     */
    private $teamMemberBookingProfiles;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Team Member Booking Profiles.
     * The returned team members' booking profiles, as a map with `team_member_id` as the key and
     * [TeamMemberBookingProfile](entity:TeamMemberBookingProfile) the value.
     *
     * @return array<string,RetrieveTeamMemberBookingProfileResponse>|null
     */
    public function getTeamMemberBookingProfiles(): ?array
    {
        return $this->teamMemberBookingProfiles;
    }

    /**
     * Sets Team Member Booking Profiles.
     * The returned team members' booking profiles, as a map with `team_member_id` as the key and
     * [TeamMemberBookingProfile](entity:TeamMemberBookingProfile) the value.
     *
     * @maps team_member_booking_profiles
     *
     * @param array<string,RetrieveTeamMemberBookingProfileResponse>|null $teamMemberBookingProfiles
     */
    public function setTeamMemberBookingProfiles(?array $teamMemberBookingProfiles): void
    {
        $this->teamMemberBookingProfiles = $teamMemberBookingProfiles;
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
        if (isset($this->teamMemberBookingProfiles)) {
            $json['team_member_booking_profiles'] = $this->teamMemberBookingProfiles;
        }
        if (isset($this->errors)) {
            $json['errors']                       = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
