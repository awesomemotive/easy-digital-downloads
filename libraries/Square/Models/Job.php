<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a job that can be assigned to [team members]($m/TeamMember). This object defines the
 * job's title and tip eligibility. Compensation is defined in a [job assignment]($m/JobAssignment)
 * in a team member's wage setting.
 */
class Job implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var array
     */
    private $title = [];

    /**
     * @var array
     */
    private $isTipEligible = [];

    /**
     * @var string|null
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * @var int|null
     */
    private $version;

    /**
     * Returns Id.
     * **Read only** The unique Square-assigned ID of the job. If you need a job ID for an API request,
     * call [ListJobs](api-endpoint:Team-ListJobs) or use the ID returned when you created the job.
     * You can also get job IDs from a team member's wage setting.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * **Read only** The unique Square-assigned ID of the job. If you need a job ID for an API request,
     * call [ListJobs](api-endpoint:Team-ListJobs) or use the ID returned when you created the job.
     * You can also get job IDs from a team member's wage setting.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Title.
     * The title of the job.
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
     * The title of the job.
     *
     * @maps title
     */
    public function setTitle(?string $title): void
    {
        $this->title['value'] = $title;
    }

    /**
     * Unsets Title.
     * The title of the job.
     */
    public function unsetTitle(): void
    {
        $this->title = [];
    }

    /**
     * Returns Is Tip Eligible.
     * Indicates whether team members can earn tips for the job.
     */
    public function getIsTipEligible(): ?bool
    {
        if (count($this->isTipEligible) == 0) {
            return null;
        }
        return $this->isTipEligible['value'];
    }

    /**
     * Sets Is Tip Eligible.
     * Indicates whether team members can earn tips for the job.
     *
     * @maps is_tip_eligible
     */
    public function setIsTipEligible(?bool $isTipEligible): void
    {
        $this->isTipEligible['value'] = $isTipEligible;
    }

    /**
     * Unsets Is Tip Eligible.
     * Indicates whether team members can earn tips for the job.
     */
    public function unsetIsTipEligible(): void
    {
        $this->isTipEligible = [];
    }

    /**
     * Returns Created At.
     * The timestamp when the job was created, in RFC 3339 format.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Sets Created At.
     * The timestamp when the job was created, in RFC 3339 format.
     *
     * @maps created_at
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns Updated At.
     * The timestamp when the job was last updated, in RFC 3339 format.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The timestamp when the job was last updated, in RFC 3339 format.
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Returns Version.
     * **Read only** The current version of the job. Include this field in `UpdateJob` requests to enable
     * [optimistic concurrency](https://developer.squareup.com/docs/working-with-apis/optimistic-
     * concurrency)
     * control and avoid overwrites from concurrent requests. Requests fail if the provided version
     * doesn't
     * match the server version at the time of the request.
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    /**
     * Sets Version.
     * **Read only** The current version of the job. Include this field in `UpdateJob` requests to enable
     * [optimistic concurrency](https://developer.squareup.com/docs/working-with-apis/optimistic-
     * concurrency)
     * control and avoid overwrites from concurrent requests. Requests fail if the provided version
     * doesn't
     * match the server version at the time of the request.
     *
     * @maps version
     */
    public function setVersion(?int $version): void
    {
        $this->version = $version;
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
            $json['id']              = $this->id;
        }
        if (!empty($this->title)) {
            $json['title']           = $this->title['value'];
        }
        if (!empty($this->isTipEligible)) {
            $json['is_tip_eligible'] = $this->isTipEligible['value'];
        }
        if (isset($this->createdAt)) {
            $json['created_at']      = $this->createdAt;
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']      = $this->updatedAt;
        }
        if (isset($this->version)) {
            $json['version']         = $this->version;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
