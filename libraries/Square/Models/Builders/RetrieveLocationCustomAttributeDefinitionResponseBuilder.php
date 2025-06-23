<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveLocationCustomAttributeDefinitionResponse;

/**
 * Builder for model RetrieveLocationCustomAttributeDefinitionResponse
 *
 * @see RetrieveLocationCustomAttributeDefinitionResponse
 */
class RetrieveLocationCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var RetrieveLocationCustomAttributeDefinitionResponse
     */
    private $instance;

    private function __construct(RetrieveLocationCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Location Custom Attribute Definition Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveLocationCustomAttributeDefinitionResponse());
    }

    /**
     * Sets custom attribute definition field.
     *
     * @param CustomAttributeDefinition|null $value
     */
    public function customAttributeDefinition(?CustomAttributeDefinition $value): self
    {
        $this->instance->setCustomAttributeDefinition($value);
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
     * Initializes a new Retrieve Location Custom Attribute Definition Response object.
     */
    public function build(): RetrieveLocationCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
