<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Job;
use EDD\Vendor\Square\Models\UpdateJobRequest;

/**
 * Builder for model UpdateJobRequest
 *
 * @see UpdateJobRequest
 */
class UpdateJobRequestBuilder
{
    /**
     * @var UpdateJobRequest
     */
    private $instance;

    private function __construct(UpdateJobRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Job Request Builder object.
     *
     * @param Job $job
     */
    public static function init(Job $job): self
    {
        return new self(new UpdateJobRequest($job));
    }

    /**
     * Initializes a new Update Job Request object.
     */
    public function build(): UpdateJobRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
