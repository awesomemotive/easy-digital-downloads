<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\ShiftWage;

/**
 * Builder for model ShiftWage
 *
 * @see ShiftWage
 */
class ShiftWageBuilder
{
    /**
     * @var ShiftWage
     */
    private $instance;

    private function __construct(ShiftWage $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Shift Wage Builder object.
     */
    public static function init(): self
    {
        return new self(new ShiftWage());
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
     * Initializes a new Shift Wage object.
     */
    public function build(): ShiftWage
    {
        return CoreHelper::clone($this->instance);
    }
}
