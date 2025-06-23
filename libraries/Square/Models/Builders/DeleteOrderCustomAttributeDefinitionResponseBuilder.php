<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteOrderCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteOrderCustomAttributeDefinitionResponse
 *
 * @see DeleteOrderCustomAttributeDefinitionResponse
 */
class DeleteOrderCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var DeleteOrderCustomAttributeDefinitionResponse
     */
    private $instance;

    private function __construct(DeleteOrderCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Order Custom Attribute Definition Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteOrderCustomAttributeDefinitionResponse());
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
     * Initializes a new Delete Order Custom Attribute Definition Response object.
     */
    public function build(): DeleteOrderCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
