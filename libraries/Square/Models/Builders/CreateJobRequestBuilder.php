<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateJobRequest;
use EDD\Vendor\Square\Models\Job;

/**
 * Builder for model CreateJobRequest
 *
 * @see CreateJobRequest
 */
class CreateJobRequestBuilder
{
    /**
     * @var CreateJobRequest
     */
    private $instance;

    private function __construct(CreateJobRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Job Request Builder object.
     *
     * @param Job $job
     * @param string $idempotencyKey
     */
    public static function init(Job $job, string $idempotencyKey): self
    {
        return new self(new CreateJobRequest($job, $idempotencyKey));
    }

    /**
     * Initializes a new Create Job Request object.
     */
    public function build(): CreateJobRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
