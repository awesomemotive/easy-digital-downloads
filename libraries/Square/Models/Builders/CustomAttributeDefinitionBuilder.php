<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;

/**
 * Builder for model CustomAttributeDefinition
 *
 * @see CustomAttributeDefinition
 */
class CustomAttributeDefinitionBuilder
{
    /**
     * @var CustomAttributeDefinition
     */
    private $instance;

    private function __construct(CustomAttributeDefinition $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Custom Attribute Definition Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomAttributeDefinition());
    }

    /**
     * Sets key field.
     *
     * @param string|null $value
     */
    public function key(?string $value): self
    {
        $this->instance->setKey($value);
        return $this;
    }

    /**
     * Unsets key field.
     */
    public function unsetKey(): self
    {
        $this->instance->unsetKey();
        return $this;
    }

    /**
     * Sets schema field.
     *
     * @param mixed $value
     */
    public function schema($value): self
    {
        $this->instance->setSchema($value);
        return $this;
    }

    /**
     * Unsets schema field.
     */
    public function unsetSchema(): self
    {
        $this->instance->unsetSchema();
        return $this;
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Unsets description field.
     */
    public function unsetDescription(): self
    {
        $this->instance->unsetDescription();
        return $this;
    }

    /**
     * Sets visibility field.
     *
     * @param string|null $value
     */
    public function visibility(?string $value): self
    {
        $this->instance->setVisibility($value);
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Custom Attribute Definition object.
     */
    public function build(): CustomAttributeDefinition
    {
        return CoreHelper::clone($this->instance);
    }
}
