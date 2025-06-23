<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListOrderCustomAttributeDefinitionsResponse;

/**
 * Builder for model ListOrderCustomAttributeDefinitionsResponse
 *
 * @see ListOrderCustomAttributeDefinitionsResponse
 */
class ListOrderCustomAttributeDefinitionsResponseBuilder
{
    /**
     * @var ListOrderCustomAttributeDefinitionsResponse
     */
    private $instance;

    private function __construct(ListOrderCustomAttributeDefinitionsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Order Custom Attribute Definitions Response Builder object.
     *
     * @param CustomAttributeDefinition[] $customAttributeDefinitions
     */
    public static function init(array $customAttributeDefinitions): self
    {
        return new self(new ListOrderCustomAttributeDefinitionsResponse($customAttributeDefinitions));
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
     * Initializes a new List Order Custom Attribute Definitions Response object.
     */
    public function build(): ListOrderCustomAttributeDefinitionsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
