<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListLocationCustomAttributeDefinitionsResponse;

/**
 * Builder for model ListLocationCustomAttributeDefinitionsResponse
 *
 * @see ListLocationCustomAttributeDefinitionsResponse
 */
class ListLocationCustomAttributeDefinitionsResponseBuilder
{
    /**
     * @var ListLocationCustomAttributeDefinitionsResponse
     */
    private $instance;

    private function __construct(ListLocationCustomAttributeDefinitionsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Location Custom Attribute Definitions Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListLocationCustomAttributeDefinitionsResponse());
    }

    /**
     * Sets custom attribute definitions field.
     *
     * @param CustomAttributeDefinition[]|null $value
     */
    public function customAttributeDefinitions(?array $value): self
    {
        $this->instance->setCustomAttributeDefinitions($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
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
     * Initializes a new List Location Custom Attribute Definitions Response object.
     */
    public function build(): ListLocationCustomAttributeDefinitionsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
