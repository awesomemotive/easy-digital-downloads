<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TeamMemberAssignedLocations;

/**
 * Builder for model TeamMemberAssignedLocations
 *
 * @see TeamMemberAssignedLocations
 */
class TeamMemberAssignedLocationsBuilder
{
    /**
     * @var TeamMemberAssignedLocations
     */
    private $instance;

    private function __construct(TeamMemberAssignedLocations $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Team Member Assigned Locations Builder object.
     */
    public static function init(): self
    {
        return new self(new TeamMemberAssignedLocations());
    }

    /**
     * Sets assignment type field.
     *
     * @param string|null $value
     */
    public function assignmentType(?string $value): self
    {
        $this->instance->setAssignmentType($value);
        return $this;
    }

    /**
     * Sets location ids field.
     *
     * @param string[]|null $value
     */
    public function locationIds(?array $value): self
    {
        $this->instance->setLocationIds($value);
        return $this;
    }

    /**
     * Unsets location ids field.
     */
    public function unsetLocationIds(): self
    {
        $this->instance->unsetLocationIds();
        return $this;
    }

    /**
     * Initializes a new Team Member Assigned Locations object.
     */
    public function build(): TeamMemberAssignedLocations
    {
        return CoreHelper::clone($this->instance);
    }
}
