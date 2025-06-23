<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Job;
use EDD\Vendor\Square\Models\UpdateJobResponse;

/**
 * Builder for model UpdateJobResponse
 *
 * @see UpdateJobResponse
 */
class UpdateJobResponseBuilder
{
    /**
     * @var UpdateJobResponse
     */
    private $instance;

    private function __construct(UpdateJobResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Job Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateJobResponse());
    }

    /**
     * Sets job field.
     *
     * @param Job|null $value
     */
    public function job(?Job $value): self
    {
        $this->instance->setJob($value);
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
     * Initializes a new Update Job Response object.
     */
    public function build(): UpdateJobResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
