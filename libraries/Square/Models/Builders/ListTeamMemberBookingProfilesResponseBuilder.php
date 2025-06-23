<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListTeamMemberBookingProfilesResponse;
use EDD\Vendor\Square\Models\TeamMemberBookingProfile;

/**
 * Builder for model ListTeamMemberBookingProfilesResponse
 *
 * @see ListTeamMemberBookingProfilesResponse
 */
class ListTeamMemberBookingProfilesResponseBuilder
{
    /**
     * @var ListTeamMemberBookingProfilesResponse
     */
    private $instance;

    private function __construct(ListTeamMemberBookingProfilesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Team Member Booking Profiles Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListTeamMemberBookingProfilesResponse());
    }

    /**
     * Sets team member booking profiles field.
     *
     * @param TeamMemberBookingProfile[]|null $value
     */
    public function teamMemberBookingProfiles(?array $value): self
    {
        $this->instance->setTeamMemberBookingProfiles($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
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
     * Initializes a new List Team Member Booking Profiles Response object.
     */
    public function build(): ListTeamMemberBookingProfilesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
