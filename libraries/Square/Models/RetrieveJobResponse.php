<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a [RetrieveJob]($e/Team/RetrieveJob) response. Either `job` or `errors`
 * is present in the response.
 */
class RetrieveJobResponse implements \JsonSerializable
{
    /**
     * @var Job|null
     */
    private $job;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Job.
     * Represents a job that can be assigned to [team members]($m/TeamMember). This object defines the
     * job's title and tip eligibility. Compensation is defined in a [job assignment]($m/JobAssignment)
     * in a team member's wage setting.
     */
    public function getJob(): ?Job
    {
        return $this->job;
    }

    /**
     * Sets Job.
     * Represents a job that can be assigned to [team members]($m/TeamMember). This object defines the
     * job's title and tip eligibility. Compensation is defined in a [job assignment]($m/JobAssignment)
     * in a team member's wage setting.
     *
     * @maps job
     */
    public function setJob(?Job $job): void
    {
        $this->job = $job;
    }

    /**
     * Returns Errors.
     * The errors that occurred during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * The errors that occurred during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
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
        if (isset($this->job)) {
            $json['job']    = $this->job;
        }
        if (isset($this->errors)) {
            $json['errors'] = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
