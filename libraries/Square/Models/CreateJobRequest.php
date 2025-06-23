<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a [CreateJob]($e/Team/CreateJob) request.
 */
class CreateJobRequest implements \JsonSerializable
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @var string
     */
    private $idempotencyKey;

    /**
     * @param Job $job
     * @param string $idempotencyKey
     */
    public function __construct(Job $job, string $idempotencyKey)
    {
        $this->job = $job;
        $this->idempotencyKey = $idempotencyKey;
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
     * Returns Idempotency Key.
     * A unique identifier for the `CreateJob` request. Keys can be any valid string,
     * but must be unique for each request. For more information, see
     * [Idempotency](https://developer.squareup.com/docs/build-basics/common-api-patterns/idempotency).
     */
    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    /**
     * Sets Idempotency Key.
     * A unique identifier for the `CreateJob` request. Keys can be any valid string,
     * but must be unique for each request. For more information, see
     * [Idempotency](https://developer.squareup.com/docs/build-basics/common-api-patterns/idempotency).
     *
     * @required
     * @maps idempotency_key
     */
    public function setIdempotencyKey(string $idempotencyKey): void
    {
        $this->idempotencyKey = $idempotencyKey;
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
        $json['job']             = $this->job;
        $json['idempotency_key'] = $this->idempotencyKey;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
