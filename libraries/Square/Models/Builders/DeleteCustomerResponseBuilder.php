<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteCustomerResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteCustomerResponse
 *
 * @see DeleteCustomerResponse
 */
class DeleteCustomerResponseBuilder
{
    /**
     * @var DeleteCustomerResponse
     */
    private $instance;

    private function __construct(DeleteCustomerResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Customer Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteCustomerResponse());
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
     * Initializes a new Delete Customer Response object.
     */
    public function build(): DeleteCustomerResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
