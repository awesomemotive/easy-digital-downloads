<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\TeamMemberWage;

/**
 * Builder for model TeamMemberWage
 *
 * @see TeamMemberWage
 */
class TeamMemberWageBuilder
{
    /**
     * @var TeamMemberWage
     */
    private $instance;

    private function __construct(TeamMemberWage $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Team Member Wage Builder object.
     */
    public static function init(): self
    {
        return new self(new TeamMemberWage());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
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
     * Unsets team member id field.
     */
    public function unsetTeamMemberId(): self
    {
        $this->instance->unsetTeamMemberId();
        return $this;
    }

    /**
     * Sets title field.
     *
     * @param string|null $value
     */
    public function title(?string $value): self
    {
        $this->instance->setTitle($value);
        return $this;
    }

    /**
     * Unsets title field.
     */
    public function unsetTitle(): self
    {
        $this->instance->unsetTitle();
        return $this;
    }

    /**
     * Sets hourly rate field.
     *
     * @param Money|null $value
     */
    public function hourlyRate(?Money $value): self
    {
        $this->instance->setHourlyRate($value);
        return $this;
    }

    /**
     * Sets job id field.
     *
     * @param string|null $value
     */
    public function jobId(?string $value): self
    {
        $this->instance->setJobId($value);
        return $this;
    }

    /**
     * Unsets job id field.
     */
    public function unsetJobId(): self
    {
        $this->instance->unsetJobId();
        return $this;
    }

    /**
     * Sets tip eligible field.
     *
     * @param bool|null $value
     */
    public function tipEligible(?bool $value): self
    {
        $this->instance->setTipEligible($value);
        return $this;
    }

    /**
     * Unsets tip eligible field.
     */
    public function unsetTipEligible(): self
    {
        $this->instance->unsetTipEligible();
        return $this;
    }

    /**
     * Initializes a new Team Member Wage object.
     */
    public function build(): TeamMemberWage
    {
        return CoreHelper::clone($this->instance);
    }
}
