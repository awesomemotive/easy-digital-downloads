<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The hourly wage rate that a team member earns on a `Shift` for doing the job
 * specified by the `title` property of this object.
 */
class TeamMemberWage implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var array
     */
    private $teamMemberId = [];

    /**
     * @var array
     */
    private $title = [];

    /**
     * @var Money|null
     */
    private $hourlyRate;

    /**
     * @var array
     */
    private $jobId = [];

    /**
     * @var array
     */
    private $tipEligible = [];

    /**
     * Returns Id.
     * The UUID for this object.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The UUID for this object.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Team Member Id.
     * The `TeamMember` that this wage is assigned to.
     */
    public function getTeamMemberId(): ?string
    {
        if (count($this->teamMemberId) == 0) {
            return null;
        }
        return $this->teamMemberId['value'];
    }

    /**
     * Sets Team Member Id.
     * The `TeamMember` that this wage is assigned to.
     *
     * @maps team_member_id
     */
    public function setTeamMemberId(?string $teamMemberId): void
    {
        $this->teamMemberId['value'] = $teamMemberId;
    }

    /**
     * Unsets Team Member Id.
     * The `TeamMember` that this wage is assigned to.
     */
    public function unsetTeamMemberId(): void
    {
        $this->teamMemberId = [];
    }

    /**
     * Returns Title.
     * The job title that this wage relates to.
     */
    public function getTitle(): ?string
    {
        if (count($this->title) == 0) {
            return null;
        }
        return $this->title['value'];
    }

    /**
     * Sets Title.
     * The job title that this wage relates to.
     *
     * @maps title
     */
    public function setTitle(?string $title): void
    {
        $this->title['value'] = $title;
    }

    /**
     * Unsets Title.
     * The job title that this wage relates to.
     */
    public function unsetTitle(): void
    {
        $this->title = [];
    }

    /**
     * Returns Hourly Rate.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getHourlyRate(): ?Money
    {
        return $this->hourlyRate;
    }

    /**
     * Sets Hourly Rate.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps hourly_rate
     */
    public function setHourlyRate(?Money $hourlyRate): void
    {
        $this->hourlyRate = $hourlyRate;
    }

    /**
     * Returns Job Id.
     * An identifier for the job that this wage relates to. This cannot be
     * used to retrieve the job.
     */
    public function getJobId(): ?string
    {
        if (count($this->jobId) == 0) {
            return null;
        }
        return $this->jobId['value'];
    }

    /**
     * Sets Job Id.
     * An identifier for the job that this wage relates to. This cannot be
     * used to retrieve the job.
     *
     * @maps job_id
     */
    public function setJobId(?string $jobId): void
    {
        $this->jobId['value'] = $jobId;
    }

    /**
     * Unsets Job Id.
     * An identifier for the job that this wage relates to. This cannot be
     * used to retrieve the job.
     */
    public function unsetJobId(): void
    {
        $this->jobId = [];
    }

    /**
     * Returns Tip Eligible.
     * Whether team members are eligible for tips when working this job.
     */
    public function getTipEligible(): ?bool
    {
        if (count($this->tipEligible) == 0) {
            return null;
        }
        return $this->tipEligible['value'];
    }

    /**
     * Sets Tip Eligible.
     * Whether team members are eligible for tips when working this job.
     *
     * @maps tip_eligible
     */
    public function setTipEligible(?bool $tipEligible): void
    {
        $this->tipEligible['value'] = $tipEligible;
    }

    /**
     * Unsets Tip Eligible.
     * Whether team members are eligible for tips when working this job.
     */
    public function unsetTipEligible(): void
    {
        $this->tipEligible = [];
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
        if (isset($this->id)) {
            $json['id']             = $this->id;
        }
        if (!empty($this->teamMemberId)) {
            $json['team_member_id'] = $this->teamMemberId['value'];
        }
        if (!empty($this->title)) {
            $json['title']          = $this->title['value'];
        }
        if (isset($this->hourlyRate)) {
            $json['hourly_rate']    = $this->hourlyRate;
        }
        if (!empty($this->jobId)) {
            $json['job_id']         = $this->jobId['value'];
        }
        if (!empty($this->tipEligible)) {
            $json['tip_eligible']   = $this->tipEligible['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
