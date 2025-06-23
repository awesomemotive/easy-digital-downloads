<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a job assigned to a [team member]($m/TeamMember), including the compensation the team
 * member earns for the job. Job assignments are listed in the team member's [wage
 * setting]($m/WageSetting).
 */
class JobAssignment implements \JsonSerializable
{
    /**
     * @var array
     */
    private $jobTitle = [];

    /**
     * @var string
     */
    private $payType;

    /**
     * @var Money|null
     */
    private $hourlyRate;

    /**
     * @var Money|null
     */
    private $annualRate;

    /**
     * @var array
     */
    private $weeklyHours = [];

    /**
     * @var array
     */
    private $jobId = [];

    /**
     * @param string $payType
     */
    public function __construct(string $payType)
    {
        $this->payType = $payType;
    }

    /**
     * Returns Job Title.
     * The title of the job.
     */
    public function getJobTitle(): ?string
    {
        if (count($this->jobTitle) == 0) {
            return null;
        }
        return $this->jobTitle['value'];
    }

    /**
     * Sets Job Title.
     * The title of the job.
     *
     * @maps job_title
     */
    public function setJobTitle(?string $jobTitle): void
    {
        $this->jobTitle['value'] = $jobTitle;
    }

    /**
     * Unsets Job Title.
     * The title of the job.
     */
    public function unsetJobTitle(): void
    {
        $this->jobTitle = [];
    }

    /**
     * Returns Pay Type.
     * Enumerates the possible pay types that a job can be assigned.
     */
    public function getPayType(): string
    {
        return $this->payType;
    }

    /**
     * Sets Pay Type.
     * Enumerates the possible pay types that a job can be assigned.
     *
     * @required
     * @maps pay_type
     */
    public function setPayType(string $payType): void
    {
        $this->payType = $payType;
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
     * Returns Annual Rate.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getAnnualRate(): ?Money
    {
        return $this->annualRate;
    }

    /**
     * Sets Annual Rate.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps annual_rate
     */
    public function setAnnualRate(?Money $annualRate): void
    {
        $this->annualRate = $annualRate;
    }

    /**
     * Returns Weekly Hours.
     * The planned hours per week for the job. Set if the job `PayType` is `SALARY`.
     */
    public function getWeeklyHours(): ?int
    {
        if (count($this->weeklyHours) == 0) {
            return null;
        }
        return $this->weeklyHours['value'];
    }

    /**
     * Sets Weekly Hours.
     * The planned hours per week for the job. Set if the job `PayType` is `SALARY`.
     *
     * @maps weekly_hours
     */
    public function setWeeklyHours(?int $weeklyHours): void
    {
        $this->weeklyHours['value'] = $weeklyHours;
    }

    /**
     * Unsets Weekly Hours.
     * The planned hours per week for the job. Set if the job `PayType` is `SALARY`.
     */
    public function unsetWeeklyHours(): void
    {
        $this->weeklyHours = [];
    }

    /**
     * Returns Job Id.
     * The ID of the [job]($m/Job).
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
     * The ID of the [job]($m/Job).
     *
     * @maps job_id
     */
    public function setJobId(?string $jobId): void
    {
        $this->jobId['value'] = $jobId;
    }

    /**
     * Unsets Job Id.
     * The ID of the [job]($m/Job).
     */
    public function unsetJobId(): void
    {
        $this->jobId = [];
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
        if (!empty($this->jobTitle)) {
            $json['job_title']    = $this->jobTitle['value'];
        }
        $json['pay_type']         = $this->payType;
        if (isset($this->hourlyRate)) {
            $json['hourly_rate']  = $this->hourlyRate;
        }
        if (isset($this->annualRate)) {
            $json['annual_rate']  = $this->annualRate;
        }
        if (!empty($this->weeklyHours)) {
            $json['weekly_hours'] = $this->weeklyHours['value'];
        }
        if (!empty($this->jobId)) {
            $json['job_id']       = $this->jobId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
