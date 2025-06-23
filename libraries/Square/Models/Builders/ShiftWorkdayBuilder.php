<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DateRange;
use EDD\Vendor\Square\Models\ShiftWorkday;

/**
 * Builder for model ShiftWorkday
 *
 * @see ShiftWorkday
 */
class ShiftWorkdayBuilder
{
    /**
     * @var ShiftWorkday
     */
    private $instance;

    private function __construct(ShiftWorkday $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Shift Workday Builder object.
     */
    public static function init(): self
    {
        return new self(new ShiftWorkday());
    }

    /**
     * Sets date range field.
     *
     * @param DateRange|null $value
     */
    public function dateRange(?DateRange $value): self
    {
        $this->instance->setDateRange($value);
        return $this;
    }

    /**
     * Sets match shifts by field.
     *
     * @param string|null $value
     */
    public function matchShiftsBy(?string $value): self
    {
        $this->instance->setMatchShiftsBy($value);
        return $this;
    }

    /**
     * Sets default timezone field.
     *
     * @param string|null $value
     */
    public function defaultTimezone(?string $value): self
    {
        $this->instance->setDefaultTimezone($value);
        return $this;
    }

    /**
     * Unsets default timezone field.
     */
    public function unsetDefaultTimezone(): self
    {
        $this->instance->unsetDefaultTimezone();
        return $this;
    }

    /**
     * Initializes a new Shift Workday object.
     */
    public function build(): ShiftWorkday
    {
        return CoreHelper::clone($this->instance);
    }
}
