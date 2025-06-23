<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\JobAssignment;
use EDD\Vendor\Square\Models\WageSetting;

/**
 * Builder for model WageSetting
 *
 * @see WageSetting
 */
class WageSettingBuilder
{
    /**
     * @var WageSetting
     */
    private $instance;

    private function __construct(WageSetting $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Wage Setting Builder object.
     */
    public static function init(): self
    {
        return new self(new WageSetting());
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
     * Sets job assignments field.
     *
     * @param JobAssignment[]|null $value
     */
    public function jobAssignments(?array $value): self
    {
        $this->instance->setJobAssignments($value);
        return $this;
    }

    /**
     * Unsets job assignments field.
     */
    public function unsetJobAssignments(): self
    {
        $this->instance->unsetJobAssignments();
        return $this;
    }

    /**
     * Sets is overtime exempt field.
     *
     * @param bool|null $value
     */
    public function isOvertimeExempt(?bool $value): self
    {
        $this->instance->setIsOvertimeExempt($value);
        return $this;
    }

    /**
     * Unsets is overtime exempt field.
     */
    public function unsetIsOvertimeExempt(): self
    {
        $this->instance->unsetIsOvertimeExempt();
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Wage Setting object.
     */
    public function build(): WageSetting
    {
        return CoreHelper::clone($this->instance);
    }
}
