<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Customer;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveCustomerResponse;

/**
 * Builder for model RetrieveCustomerResponse
 *
 * @see RetrieveCustomerResponse
 */
class RetrieveCustomerResponseBuilder
{
    /**
     * @var RetrieveCustomerResponse
     */
    private $instance;

    private function __construct(RetrieveCustomerResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Customer Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveCustomerResponse());
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
     * Sets customer field.
     *
     * @param Customer|null $value
     */
    public function customer(?Customer $value): self
    {
        $this->instance->setCustomer($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Customer Response object.
     */
    public function build(): RetrieveCustomerResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
