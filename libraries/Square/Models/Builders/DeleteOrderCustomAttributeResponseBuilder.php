<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteOrderCustomAttributeResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteOrderCustomAttributeResponse
 *
 * @see DeleteOrderCustomAttributeResponse
 */
class DeleteOrderCustomAttributeResponseBuilder
{
    /**
     * @var DeleteOrderCustomAttributeResponse
     */
    private $instance;

    private function __construct(DeleteOrderCustomAttributeResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Order Custom Attribute Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteOrderCustomAttributeResponse());
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
     * Initializes a new Delete Order Custom Attribute Response object.
     */
    public function build(): DeleteOrderCustomAttributeResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
