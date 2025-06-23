<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerGroup;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveCustomerGroupResponse;

/**
 * Builder for model RetrieveCustomerGroupResponse
 *
 * @see RetrieveCustomerGroupResponse
 */
class RetrieveCustomerGroupResponseBuilder
{
    /**
     * @var RetrieveCustomerGroupResponse
     */
    private $instance;

    private function __construct(RetrieveCustomerGroupResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Customer Group Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveCustomerGroupResponse());
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
     * Sets group field.
     *
     * @param CustomerGroup|null $value
     */
    public function group(?CustomerGroup $value): self
    {
        $this->instance->setGroup($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Customer Group Response object.
     */
    public function build(): RetrieveCustomerGroupResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
