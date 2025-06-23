<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogItemModifierListInfo;
use EDD\Vendor\Square\Models\CatalogModifierOverride;

/**
 * Builder for model CatalogItemModifierListInfo
 *
 * @see CatalogItemModifierListInfo
 */
class CatalogItemModifierListInfoBuilder
{
    /**
     * @var CatalogItemModifierListInfo
     */
    private $instance;

    private function __construct(CatalogItemModifierListInfo $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Item Modifier List Info Builder object.
     *
     * @param string $modifierListId
     */
    public static function init(string $modifierListId): self
    {
        return new self(new CatalogItemModifierListInfo($modifierListId));
    }

    /**
     * Sets modifier overrides field.
     *
     * @param CatalogModifierOverride[]|null $value
     */
    public function modifierOverrides(?array $value): self
    {
        $this->instance->setModifierOverrides($value);
        return $this;
    }

    /**
     * Unsets modifier overrides field.
     */
    public function unsetModifierOverrides(): self
    {
        $this->instance->unsetModifierOverrides();
        return $this;
    }

    /**
     * Sets min selected modifiers field.
     *
     * @param int|null $value
     */
    public function minSelectedModifiers(?int $value): self
    {
        $this->instance->setMinSelectedModifiers($value);
        return $this;
    }

    /**
     * Unsets min selected modifiers field.
     */
    public function unsetMinSelectedModifiers(): self
    {
        $this->instance->unsetMinSelectedModifiers();
        return $this;
    }

    /**
     * Sets max selected modifiers field.
     *
     * @param int|null $value
     */
    public function maxSelectedModifiers(?int $value): self
    {
        $this->instance->setMaxSelectedModifiers($value);
        return $this;
    }

    /**
     * Unsets max selected modifiers field.
     */
    public function unsetMaxSelectedModifiers(): self
    {
        $this->instance->unsetMaxSelectedModifiers();
        return $this;
    }

    /**
     * Sets enabled field.
     *
     * @param bool|null $value
     */
    public function enabled(?bool $value): self
    {
        $this->instance->setEnabled($value);
        return $this;
    }

    /**
     * Unsets enabled field.
     */
    public function unsetEnabled(): self
    {
        $this->instance->unsetEnabled();
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
     * Initializes a new Catalog Item Modifier List Info object.
     */
    public function build(): CatalogItemModifierListInfo
    {
        return CoreHelper::clone($this->instance);
    }
}
