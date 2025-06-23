<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateLocationCustomAttributeDefinitionRequest;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;

/**
 * Builder for model CreateLocationCustomAttributeDefinitionRequest
 *
 * @see CreateLocationCustomAttributeDefinitionRequest
 */
class CreateLocationCustomAttributeDefinitionRequestBuilder
{
    /**
     * @var CreateLocationCustomAttributeDefinitionRequest
     */
    private $instance;

    private function __construct(CreateLocationCustomAttributeDefinitionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Location Custom Attribute Definition Request Builder object.
     *
     * @param CustomAttributeDefinition $customAttributeDefinition
     */
    public static function init(CustomAttributeDefinition $customAttributeDefinition): self
    {
        return new self(new CreateLocationCustomAttributeDefinitionRequest($customAttributeDefinition));
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }

    /**
     * Initializes a new Create Location Custom Attribute Definition Request object.
     */
    public function build(): CreateLocationCustomAttributeDefinitionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
