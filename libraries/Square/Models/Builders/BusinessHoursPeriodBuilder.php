<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BusinessHoursPeriod;

/**
 * Builder for model BusinessHoursPeriod
 *
 * @see BusinessHoursPeriod
 */
class BusinessHoursPeriodBuilder
{
    /**
     * @var BusinessHoursPeriod
     */
    private $instance;

    private function __construct(BusinessHoursPeriod $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Business Hours Period Builder object.
     */
    public static function init(): self
    {
        return new self(new BusinessHoursPeriod());
    }

    /**
     * Sets day of week field.
     *
     * @param string|null $value
     */
    public function dayOfWeek(?string $value): self
    {
        $this->instance->setDayOfWeek($value);
        return $this;
    }

    /**
     * Sets start local time field.
     *
     * @param string|null $value
     */
    public function startLocalTime(?string $value): self
    {
        $this->instance->setStartLocalTime($value);
        return $this;
    }

    /**
     * Unsets start local time field.
     */
    public function unsetStartLocalTime(): self
    {
        $this->instance->unsetStartLocalTime();
        return $this;
    }

    /**
     * Sets end local time field.
     *
     * @param string|null $value
     */
    public function endLocalTime(?string $value): self
    {
        $this->instance->setEndLocalTime($value);
        return $this;
    }

    /**
     * Unsets end local time field.
     */
    public function unsetEndLocalTime(): self
    {
        $this->instance->unsetEndLocalTime();
        return $this;
    }

    /**
     * Initializes a new Business Hours Period object.
     */
    public function build(): BusinessHoursPeriod
    {
        return CoreHelper::clone($this->instance);
    }
}
