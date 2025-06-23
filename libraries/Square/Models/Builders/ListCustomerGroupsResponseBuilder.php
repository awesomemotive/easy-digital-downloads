<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerGroup;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListCustomerGroupsResponse;

/**
 * Builder for model ListCustomerGroupsResponse
 *
 * @see ListCustomerGroupsResponse
 */
class ListCustomerGroupsResponseBuilder
{
    /**
     * @var ListCustomerGroupsResponse
     */
    private $instance;

    private function __construct(ListCustomerGroupsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Customer Groups Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCustomerGroupsResponse());
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
     * Sets groups field.
     *
     * @param CustomerGroup[]|null $value
     */
    public function groups(?array $value): self
    {
        $this->instance->setGroups($value);
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
     * Initializes a new List Customer Groups Response object.
     */
    public function build(): ListCustomerGroupsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
