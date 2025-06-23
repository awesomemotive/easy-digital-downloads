<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ShiftFilter;
use EDD\Vendor\Square\Models\ShiftWorkday;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model ShiftFilter
 *
 * @see ShiftFilter
 */
class ShiftFilterBuilder
{
    /**
     * @var ShiftFilter
     */
    private $instance;

    private function __construct(ShiftFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Shift Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new ShiftFilter());
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
     * Sets employee ids field.
     *
     * @param string[]|null $value
     */
    public function employeeIds(?array $value): self
    {
        $this->instance->setEmployeeIds($value);
        return $this;
    }

    /**
     * Unsets employee ids field.
     */
    public function unsetEmployeeIds(): self
    {
        $this->instance->unsetEmployeeIds();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets start field.
     *
     * @param TimeRange|null $value
     */
    public function start(?TimeRange $value): self
    {
        $this->instance->setStart($value);
        return $this;
    }

    /**
     * Sets end field.
     *
     * @param TimeRange|null $value
     */
    public function end(?TimeRange $value): self
    {
        $this->instance->setEnd($value);
        return $this;
    }

    /**
     * Sets workday field.
     *
     * @param ShiftWorkday|null $value
     */
    public function workday(?ShiftWorkday $value): self
    {
        $this->instance->setWorkday($value);
        return $this;
    }

    /**
     * Sets team member ids field.
     *
     * @param string[]|null $value
     */
    public function teamMemberIds(?array $value): self
    {
        $this->instance->setTeamMemberIds($value);
        return $this;
    }

    /**
     * Unsets team member ids field.
     */
    public function unsetTeamMemberIds(): self
    {
        $this->instance->unsetTeamMemberIds();
        return $this;
    }

    /**
     * Initializes a new Shift Filter object.
     */
    public function build(): ShiftFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
