<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateCustomerCustomAttributeDefinitionRequest;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;

/**
 * Builder for model CreateCustomerCustomAttributeDefinitionRequest
 *
 * @see CreateCustomerCustomAttributeDefinitionRequest
 */
class CreateCustomerCustomAttributeDefinitionRequestBuilder
{
    /**
     * @var CreateCustomerCustomAttributeDefinitionRequest
     */
    private $instance;

    private function __construct(CreateCustomerCustomAttributeDefinitionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Customer Custom Attribute Definition Request Builder object.
     *
     * @param CustomAttributeDefinition $customAttributeDefinition
     */
    public static function init(CustomAttributeDefinition $customAttributeDefinition): self
    {
        return new self(new CreateCustomerCustomAttributeDefinitionRequest($customAttributeDefinition));
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
     * Initializes a new Create Customer Custom Attribute Definition Request object.
     */
    public function build(): CreateCustomerCustomAttributeDefinitionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
