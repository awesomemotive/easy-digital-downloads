<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Request payload for the
 * [BulkRetrieveTeamMemberBookingProfiles]($e/Bookings/BulkRetrieveTeamMemberBookingProfiles) endpoint.
 */
class BulkRetrieveTeamMemberBookingProfilesRequest implements \JsonSerializable
{
    /**
     * @var string[]
     */
    private $teamMemberIds;

    /**
     * @param string[] $teamMemberIds
     */
    public function __construct(array $teamMemberIds)
    {
        $this->teamMemberIds = $teamMemberIds;
    }

    /**
     * Returns Team Member Ids.
     * A non-empty list of IDs of team members whose booking profiles you want to retrieve.
     *
     * @return string[]
     */
    public function getTeamMemberIds(): array
    {
        return $this->teamMemberIds;
    }

    /**
     * Sets Team Member Ids.
     * A non-empty list of IDs of team members whose booking profiles you want to retrieve.
     *
     * @required
     * @maps team_member_ids
     *
     * @param string[] $teamMemberIds
     */
    public function setTeamMemberIds(array $teamMemberIds): void
    {
        $this->teamMemberIds = $teamMemberIds;
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
        $json['team_member_ids'] = $this->teamMemberIds;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
