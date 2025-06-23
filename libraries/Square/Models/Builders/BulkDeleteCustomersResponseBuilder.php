<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteCustomersResponse;
use EDD\Vendor\Square\Models\DeleteCustomerResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkDeleteCustomersResponse
 *
 * @see BulkDeleteCustomersResponse
 */
class BulkDeleteCustomersResponseBuilder
{
    /**
     * @var BulkDeleteCustomersResponse
     */
    private $instance;

    private function __construct(BulkDeleteCustomersResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Customers Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkDeleteCustomersResponse());
    }

    /**
     * Sets responses field.
     *
     * @param array<string,DeleteCustomerResponse>|null $value
     */
    public function responses(?array $value): self
    {
        $this->instance->setResponses($value);
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
     * Initializes a new Bulk Delete Customers Response object.
     */
    public function build(): BulkDeleteCustomersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
