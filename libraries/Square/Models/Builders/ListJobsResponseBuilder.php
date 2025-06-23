<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Job;
use EDD\Vendor\Square\Models\ListJobsResponse;

/**
 * Builder for model ListJobsResponse
 *
 * @see ListJobsResponse
 */
class ListJobsResponseBuilder
{
    /**
     * @var ListJobsResponse
     */
    private $instance;

    private function __construct(ListJobsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Jobs Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListJobsResponse());
    }

    /**
     * Sets jobs field.
     *
     * @param Job[]|null $value
     */
    public function jobs(?array $value): self
    {
        $this->instance->setJobs($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new List Jobs Response object.
     */
    public function build(): ListJobsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
