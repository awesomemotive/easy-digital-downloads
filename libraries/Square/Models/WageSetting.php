<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents information about the overtime exemption status, job assignments, and compensation
 * for a [team member]($m/TeamMember).
 */
class WageSetting implements \JsonSerializable
{
    /**
     * @var array
     */
    private $teamMemberId = [];

    /**
     * @var array
     */
    private $jobAssignments = [];

    /**
     * @var array
     */
    private $isOvertimeExempt = [];

    /**
     * @var int|null
     */
    private $version;

    /**
     * @var string|null
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * Returns Team Member Id.
     * The ID of the team member associated with the wage setting.
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
     * The ID of the team member associated with the wage setting.
     *
     * @maps team_member_id
     */
    public function setTeamMemberId(?string $teamMemberId): void
    {
        $this->teamMemberId['value'] = $teamMemberId;
    }

    /**
     * Unsets Team Member Id.
     * The ID of the team member associated with the wage setting.
     */
    public function unsetTeamMemberId(): void
    {
        $this->teamMemberId = [];
    }

    /**
     * Returns Job Assignments.
     * **Required** The ordered list of jobs that the team member is assigned to.
     * The first job assignment is considered the team member's primary job.
     *
     * @return JobAssignment[]|null
     */
    public function getJobAssignments(): ?array
    {
        if (count($this->jobAssignments) == 0) {
            return null;
        }
        return $this->jobAssignments['value'];
    }

    /**
     * Sets Job Assignments.
     * **Required** The ordered list of jobs that the team member is assigned to.
     * The first job assignment is considered the team member's primary job.
     *
     * @maps job_assignments
     *
     * @param JobAssignment[]|null $jobAssignments
     */
    public function setJobAssignments(?array $jobAssignments): void
    {
        $this->jobAssignments['value'] = $jobAssignments;
    }

    /**
     * Unsets Job Assignments.
     * **Required** The ordered list of jobs that the team member is assigned to.
     * The first job assignment is considered the team member's primary job.
     */
    public function unsetJobAssignments(): void
    {
        $this->jobAssignments = [];
    }

    /**
     * Returns Is Overtime Exempt.
     * Whether the team member is exempt from the overtime rules of the seller's country.
     */
    public function getIsOvertimeExempt(): ?bool
    {
        if (count($this->isOvertimeExempt) == 0) {
            return null;
        }
        return $this->isOvertimeExempt['value'];
    }

    /**
     * Sets Is Overtime Exempt.
     * Whether the team member is exempt from the overtime rules of the seller's country.
     *
     * @maps is_overtime_exempt
     */
    public function setIsOvertimeExempt(?bool $isOvertimeExempt): void
    {
        $this->isOvertimeExempt['value'] = $isOvertimeExempt;
    }

    /**
     * Unsets Is Overtime Exempt.
     * Whether the team member is exempt from the overtime rules of the seller's country.
     */
    public function unsetIsOvertimeExempt(): void
    {
        $this->isOvertimeExempt = [];
    }

    /**
     * Returns Version.
     * **Read only** Used for resolving concurrency issues. The request fails if the version
     * provided does not match the server version at the time of the request. If not provided,
     * EDD\Vendor\Square executes a blind write, potentially overwriting data from another write. For more information,
     * see [optimistic concurrency](https://developer.squareup.com/docs/working-with-apis/optimistic-
     * concurrency).
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    /**
     * Sets Version.
     * **Read only** Used for resolving concurrency issues. The request fails if the version
     * provided does not match the server version at the time of the request. If not provided,
     * EDD\Vendor\Square executes a blind write, potentially overwriting data from another write. For more information,
     * see [optimistic concurrency](https://developer.squareup.com/docs/working-with-apis/optimistic-
     * concurrency).
     *
     * @maps version
     */
    public function setVersion(?int $version): void
    {
        $this->version = $version;
    }

    /**
     * Returns Created At.
     * The timestamp when the wage setting was created, in RFC 3339 format.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Sets Created At.
     * The timestamp when the wage setting was created, in RFC 3339 format.
     *
     * @maps created_at
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns Updated At.
     * The timestamp when the wage setting was last updated, in RFC 3339 format.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The timestamp when the wage setting was last updated, in RFC 3339 format.
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
        if (!empty($this->teamMemberId)) {
            $json['team_member_id']     = $this->teamMemberId['value'];
        }
        if (!empty($this->jobAssignments)) {
            $json['job_assignments']    = $this->jobAssignments['value'];
        }
        if (!empty($this->isOvertimeExempt)) {
            $json['is_overtime_exempt'] = $this->isOvertimeExempt['value'];
        }
        if (isset($this->version)) {
            $json['version']            = $this->version;
        }
        if (isset($this->createdAt)) {
            $json['created_at']         = $this->createdAt;
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']         = $this->updatedAt;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
