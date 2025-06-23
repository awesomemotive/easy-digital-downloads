<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a [ListJobs]($e/Team/ListJobs) response. Either `jobs` or `errors`
 * is present in the response. If additional results are available, the `cursor` field is also present.
 */
class ListJobsResponse implements \JsonSerializable
{
    /**
     * @var Job[]|null
     */
    private $jobs;

    /**
     * @var string|null
     */
    private $cursor;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Jobs.
     * The retrieved jobs. A single paged response contains up to 100 jobs.
     *
     * @return Job[]|null
     */
    public function getJobs(): ?array
    {
        return $this->jobs;
    }

    /**
     * Sets Jobs.
     * The retrieved jobs. A single paged response contains up to 100 jobs.
     *
     * @maps jobs
     *
     * @param Job[]|null $jobs
     */
    public function setJobs(?array $jobs): void
    {
        $this->jobs = $jobs;
    }

    /**
     * Returns Cursor.
     * An opaque cursor used to retrieve the next page of results. This field is present only
     * if the request succeeded and additional results are available. For more information, see
     * [Pagination](https://developer.squareup.com/docs/build-basics/common-api-patterns/pagination).
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Sets Cursor.
     * An opaque cursor used to retrieve the next page of results. This field is present only
     * if the request succeeded and additional results are available. For more information, see
     * [Pagination](https://developer.squareup.com/docs/build-basics/common-api-patterns/pagination).
     *
     * @maps cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
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
        if (isset($this->jobs)) {
            $json['jobs']   = $this->jobs;
        }
        if (isset($this->cursor)) {
            $json['cursor'] = $this->cursor;
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
