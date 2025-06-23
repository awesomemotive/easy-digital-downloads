<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkRetrieveTeamMemberBookingProfilesResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveTeamMemberBookingProfileResponse;

/**
 * Builder for model BulkRetrieveTeamMemberBookingProfilesResponse
 *
 * @see BulkRetrieveTeamMemberBookingProfilesResponse
 */
class BulkRetrieveTeamMemberBookingProfilesResponseBuilder
{
    /**
     * @var BulkRetrieveTeamMemberBookingProfilesResponse
     */
    private $instance;

    private function __construct(BulkRetrieveTeamMemberBookingProfilesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Retrieve Team Member Booking Profiles Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkRetrieveTeamMemberBookingProfilesResponse());
    }

    /**
     * Sets team member booking profiles field.
     *
     * @param array<string,RetrieveTeamMemberBookingProfileResponse>|null $value
     */
    public function teamMemberBookingProfiles(?array $value): self
    {
        $this->instance->setTeamMemberBookingProfiles($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new Bulk Retrieve Team Member Booking Profiles Response object.
     */
    public function build(): BulkRetrieveTeamMemberBookingProfilesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
