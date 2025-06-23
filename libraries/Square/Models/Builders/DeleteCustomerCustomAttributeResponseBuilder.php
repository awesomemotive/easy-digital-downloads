<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteCustomerCustomAttributeResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteCustomerCustomAttributeResponse
 *
 * @see DeleteCustomerCustomAttributeResponse
 */
class DeleteCustomerCustomAttributeResponseBuilder
{
    /**
     * @var DeleteCustomerCustomAttributeResponse
     */
    private $instance;

    private function __construct(DeleteCustomerCustomAttributeResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Customer Custom Attribute Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteCustomerCustomAttributeResponse());
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
     * Initializes a new Delete Customer Custom Attribute Response object.
     */
    public function build(): DeleteCustomerCustomAttributeResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
