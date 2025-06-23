<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListJobsRequest;

/**
 * Builder for model ListJobsRequest
 *
 * @see ListJobsRequest
 */
class ListJobsRequestBuilder
{
    /**
     * @var ListJobsRequest
     */
    private $instance;

    private function __construct(ListJobsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Jobs Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListJobsRequest());
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
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Initializes a new List Jobs Request object.
     */
    public function build(): ListJobsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
