<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents an [UpdateJob]($e/Team/UpdateJob) request.
 */
class UpdateJobRequest implements \JsonSerializable
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Returns Job.
     * Represents a job that can be assigned to [team members]($m/TeamMember). This object defines the
     * job's title and tip eligibility. Compensation is defined in a [job assignment]($m/JobAssignment)
     * in a team member's wage setting.
     */
    public function getJob(): Job
    {
        return $this->job;
    }

    /**
     * Sets Job.
     * Represents a job that can be assigned to [team members]($m/TeamMember). This object defines the
     * job's title and tip eligibility. Compensation is defined in a [job assignment]($m/JobAssignment)
     * in a team member's wage setting.
     *
     * @required
     * @maps job
     */
    public function setJob(Job $job): void
    {
        $this->job = $job;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        $json['job'] = $this->job;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
