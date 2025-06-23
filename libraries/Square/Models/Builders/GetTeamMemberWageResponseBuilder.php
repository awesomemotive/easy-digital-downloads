<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\GetTeamMemberWageResponse;
use EDD\Vendor\Square\Models\TeamMemberWage;

/**
 * Builder for model GetTeamMemberWageResponse
 *
 * @see GetTeamMemberWageResponse
 */
class GetTeamMemberWageResponseBuilder
{
    /**
     * @var GetTeamMemberWageResponse
     */
    private $instance;

    private function __construct(GetTeamMemberWageResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Get Team Member Wage Response Builder object.
     */
    public static function init(): self
    {
        return new self(new GetTeamMemberWageResponse());
    }

    /**
     * Sets team member wage field.
     *
     * @param TeamMemberWage|null $value
     */
    public function teamMemberWage(?TeamMemberWage $value): self
    {
        $this->instance->setTeamMemberWage($value);
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
     * Initializes a new Get Team Member Wage Response object.
     */
    public function build(): GetTeamMemberWageResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
