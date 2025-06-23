<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerGroup;
use EDD\Vendor\Square\Models\UpdateCustomerGroupRequest;

/**
 * Builder for model UpdateCustomerGroupRequest
 *
 * @see UpdateCustomerGroupRequest
 */
class UpdateCustomerGroupRequestBuilder
{
    /**
     * @var UpdateCustomerGroupRequest
     */
    private $instance;

    private function __construct(UpdateCustomerGroupRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Customer Group Request Builder object.
     *
     * @param CustomerGroup $group
     */
    public static function init(CustomerGroup $group): self
    {
        return new self(new UpdateCustomerGroupRequest($group));
    }

    /**
     * Initializes a new Update Customer Group Request object.
     */
    public function build(): UpdateCustomerGroupRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
