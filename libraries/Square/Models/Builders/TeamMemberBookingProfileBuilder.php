<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TeamMemberBookingProfile;

/**
 * Builder for model TeamMemberBookingProfile
 *
 * @see TeamMemberBookingProfile
 */
class TeamMemberBookingProfileBuilder
{
    /**
     * @var TeamMemberBookingProfile
     */
    private $instance;

    private function __construct(TeamMemberBookingProfile $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Team Member Booking Profile Builder object.
     */
    public static function init(): self
    {
        return new self(new TeamMemberBookingProfile());
    }

    /**
     * Sets team member id field.
     *
     * @param string|null $value
     */
    public function teamMemberId(?string $value): self
    {
        $this->instance->setTeamMemberId($value);
        return $this;
    }

    /**
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Sets display name field.
     *
     * @param string|null $value
     */
    public function displayName(?string $value): self
    {
        $this->instance->setDisplayName($value);
        return $this;
    }

    /**
     * Sets is bookable field.
     *
     * @param bool|null $value
     */
    public function isBookable(?bool $value): self
    {
        $this->instance->setIsBookable($value);
        return $this;
    }

    /**
     * Unsets is bookable field.
     */
    public function unsetIsBookable(): self
    {
        $this->instance->unsetIsBookable();
        return $this;
    }

    /**
     * Sets profile image url field.
     *
     * @param string|null $value
     */
    public function profileImageUrl(?string $value): self
    {
        $this->instance->setProfileImageUrl($value);
        return $this;
    }

    /**
     * Initializes a new Team Member Booking Profile object.
     */
    public function build(): TeamMemberBookingProfile
    {
        return CoreHelper::clone($this->instance);
    }
}
