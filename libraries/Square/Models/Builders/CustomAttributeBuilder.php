<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\CustomAttributeDefinition;

/**
 * Builder for model CustomAttribute
 *
 * @see CustomAttribute
 */
class CustomAttributeBuilder
{
    /**
     * @var CustomAttribute
     */
    private $instance;

    private function __construct(CustomAttribute $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Custom Attribute Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomAttribute());
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
     * Sets value field.
     *
     * @param mixed $value
     */
    public function value($value): self
    {
        $this->instance->setValue($value);
        return $this;
    }

    /**
     * Unsets value field.
     */
    public function unsetValue(): self
    {
        $this->instance->unsetValue();
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
     * Sets definition field.
     *
     * @param CustomAttributeDefinition|null $value
     */
    public function definition(?CustomAttributeDefinition $value): self
    {
        $this->instance->setDefinition($value);
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
     * Initializes a new Custom Attribute object.
     */
    public function build(): CustomAttribute
    {
        return CoreHelper::clone($this->instance);
    }
}
