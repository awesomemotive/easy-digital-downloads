<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\JobAssignment;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model JobAssignment
 *
 * @see JobAssignment
 */
class JobAssignmentBuilder
{
    /**
     * @var JobAssignment
     */
    private $instance;

    private function __construct(JobAssignment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Job Assignment Builder object.
     *
     * @param string $payType
     */
    public static function init(string $payType): self
    {
        return new self(new JobAssignment($payType));
    }

    /**
     * Sets job title field.
     *
     * @param string|null $value
     */
    public function jobTitle(?string $value): self
    {
        $this->instance->setJobTitle($value);
        return $this;
    }

    /**
     * Unsets job title field.
     */
    public function unsetJobTitle(): self
    {
        $this->instance->unsetJobTitle();
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
     * Sets annual rate field.
     *
     * @param Money|null $value
     */
    public function annualRate(?Money $value): self
    {
        $this->instance->setAnnualRate($value);
        return $this;
    }

    /**
     * Sets weekly hours field.
     *
     * @param int|null $value
     */
    public function weeklyHours(?int $value): self
    {
        $this->instance->setWeeklyHours($value);
        return $this;
    }

    /**
     * Unsets weekly hours field.
     */
    public function unsetWeeklyHours(): self
    {
        $this->instance->unsetWeeklyHours();
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
     * Initializes a new Job Assignment object.
     */
    public function build(): JobAssignment
    {
        return CoreHelper::clone($this->instance);
    }
}
