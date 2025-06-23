<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListWorkweekConfigsResponse;
use EDD\Vendor\Square\Models\WorkweekConfig;

/**
 * Builder for model ListWorkweekConfigsResponse
 *
 * @see ListWorkweekConfigsResponse
 */
class ListWorkweekConfigsResponseBuilder
{
    /**
     * @var ListWorkweekConfigsResponse
     */
    private $instance;

    private function __construct(ListWorkweekConfigsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Workweek Configs Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListWorkweekConfigsResponse());
    }

    /**
     * Sets workweek configs field.
     *
     * @param WorkweekConfig[]|null $value
     */
    public function workweekConfigs(?array $value): self
    {
        $this->instance->setWorkweekConfigs($value);
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
     * Initializes a new List Workweek Configs Response object.
     */
    public function build(): ListWorkweekConfigsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
