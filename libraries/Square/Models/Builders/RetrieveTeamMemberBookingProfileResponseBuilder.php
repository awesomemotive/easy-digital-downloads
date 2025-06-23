<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveTeamMemberBookingProfileResponse;
use EDD\Vendor\Square\Models\TeamMemberBookingProfile;

/**
 * Builder for model RetrieveTeamMemberBookingProfileResponse
 *
 * @see RetrieveTeamMemberBookingProfileResponse
 */
class RetrieveTeamMemberBookingProfileResponseBuilder
{
    /**
     * @var RetrieveTeamMemberBookingProfileResponse
     */
    private $instance;

    private function __construct(RetrieveTeamMemberBookingProfileResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Team Member Booking Profile Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveTeamMemberBookingProfileResponse());
    }

    /**
     * Sets team member booking profile field.
     *
     * @param TeamMemberBookingProfile|null $value
     */
    public function teamMemberBookingProfile(?TeamMemberBookingProfile $value): self
    {
        $this->instance->setTeamMemberBookingProfile($value);
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
     * Initializes a new Retrieve Team Member Booking Profile Response object.
     */
    public function build(): RetrieveTeamMemberBookingProfileResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
