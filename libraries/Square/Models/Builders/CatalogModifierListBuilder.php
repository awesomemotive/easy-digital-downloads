<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogModifierList;
use EDD\Vendor\Square\Models\CatalogObject;

/**
 * Builder for model CatalogModifierList
 *
 * @see CatalogModifierList
 */
class CatalogModifierListBuilder
{
    /**
     * @var CatalogModifierList
     */
    private $instance;

    private function __construct(CatalogModifierList $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Modifier List Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogModifierList());
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
     * Sets ordinal field.
     *
     * @param int|null $value
     */
    public function ordinal(?int $value): self
    {
        $this->instance->setOrdinal($value);
        return $this;
    }

    /**
     * Unsets ordinal field.
     */
    public function unsetOrdinal(): self
    {
        $this->instance->unsetOrdinal();
        return $this;
    }

    /**
     * Sets selection type field.
     *
     * @param string|null $value
     */
    public function selectionType(?string $value): self
    {
        $this->instance->setSelectionType($value);
        return $this;
    }

    /**
     * Sets modifiers field.
     *
     * @param CatalogObject[]|null $value
     */
    public function modifiers(?array $value): self
    {
        $this->instance->setModifiers($value);
        return $this;
    }

    /**
     * Unsets modifiers field.
     */
    public function unsetModifiers(): self
    {
        $this->instance->unsetModifiers();
        return $this;
    }

    /**
     * Sets image ids field.
     *
     * @param string[]|null $value
     */
    public function imageIds(?array $value): self
    {
        $this->instance->setImageIds($value);
        return $this;
    }

    /**
     * Unsets image ids field.
     */
    public function unsetImageIds(): self
    {
        $this->instance->unsetImageIds();
        return $this;
    }

    /**
     * Sets modifier type field.
     *
     * @param string|null $value
     */
    public function modifierType(?string $value): self
    {
        $this->instance->setModifierType($value);
        return $this;
    }

    /**
     * Sets max length field.
     *
     * @param int|null $value
     */
    public function maxLength(?int $value): self
    {
        $this->instance->setMaxLength($value);
        return $this;
    }

    /**
     * Unsets max length field.
     */
    public function unsetMaxLength(): self
    {
        $this->instance->unsetMaxLength();
        return $this;
    }

    /**
     * Sets text required field.
     *
     * @param bool|null $value
     */
    public function textRequired(?bool $value): self
    {
        $this->instance->setTextRequired($value);
        return $this;
    }

    /**
     * Unsets text required field.
     */
    public function unsetTextRequired(): self
    {
        $this->instance->unsetTextRequired();
        return $this;
    }

    /**
     * Sets internal name field.
     *
     * @param string|null $value
     */
    public function internalName(?string $value): self
    {
        $this->instance->setInternalName($value);
        return $this;
    }

    /**
     * Unsets internal name field.
     */
    public function unsetInternalName(): self
    {
        $this->instance->unsetInternalName();
        return $this;
    }

    /**
     * Initializes a new Catalog Modifier List object.
     */
    public function build(): CatalogModifierList
    {
        return CoreHelper::clone($this->instance);
    }
}
