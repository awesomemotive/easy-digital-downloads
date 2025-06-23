<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateCustomerGroupResponse;
use EDD\Vendor\Square\Models\CustomerGroup;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model CreateCustomerGroupResponse
 *
 * @see CreateCustomerGroupResponse
 */
class CreateCustomerGroupResponseBuilder
{
    /**
     * @var CreateCustomerGroupResponse
     */
    private $instance;

    private function __construct(CreateCustomerGroupResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Customer Group Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateCustomerGroupResponse());
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
     * Initializes a new Create Customer Group Response object.
     */
    public function build(): CreateCustomerGroupResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
