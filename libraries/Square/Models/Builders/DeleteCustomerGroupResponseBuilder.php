<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteCustomerGroupResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteCustomerGroupResponse
 *
 * @see DeleteCustomerGroupResponse
 */
class DeleteCustomerGroupResponseBuilder
{
    /**
     * @var DeleteCustomerGroupResponse
     */
    private $instance;

    private function __construct(DeleteCustomerGroupResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Customer Group Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteCustomerGroupResponse());
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
     * Initializes a new Delete Customer Group Response object.
     */
    public function build(): DeleteCustomerGroupResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
