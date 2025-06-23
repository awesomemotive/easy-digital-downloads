<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogItemOption;
use EDD\Vendor\Square\Models\CatalogObject;

/**
 * Builder for model CatalogItemOption
 *
 * @see CatalogItemOption
 */
class CatalogItemOptionBuilder
{
    /**
     * @var CatalogItemOption
     */
    private $instance;

    private function __construct(CatalogItemOption $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Option Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogItemOption());
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
     * Sets display name field.
     *
     * @param string|null $value
     */
    public function displayName(?string $value): self
    {
        $this->instance->setDisplayName($value);
        return $this;
    }

    /**
     * Unsets display name field.
     */
    public function unsetDisplayName(): self
    {
        $this->instance->unsetDisplayName();
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
     * Sets show colors field.
     *
     * @param bool|null $value
     */
    public function showColors(?bool $value): self
    {
        $this->instance->setShowColors($value);
        return $this;
    }

    /**
     * Unsets show colors field.
     */
    public function unsetShowColors(): self
    {
        $this->instance->unsetShowColors();
        return $this;
    }

    /**
     * Sets values field.
     *
     * @param CatalogObject[]|null $value
     */
    public function values(?array $value): self
    {
        $this->instance->setValues($value);
        return $this;
    }

    /**
     * Unsets values field.
     */
    public function unsetValues(): self
    {
        $this->instance->unsetValues();
        return $this;
    }

    /**
     * Initializes a new Catalog Item Option object.
     */
    public function build(): CatalogItemOption
    {
        return CoreHelper::clone($this->instance);
    }
}
