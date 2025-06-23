<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogModifier;
use EDD\Vendor\Square\Models\ModifierLocationOverrides;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CatalogModifier
 *
 * @see CatalogModifier
 */
class CatalogModifierBuilder
{
    /**
     * @var CatalogModifier
     */
    private $instance;

    private function __construct(CatalogModifier $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Modifier Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogModifier());
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
     * Sets price money field.
     *
     * @param Money|null $value
     */
    public function priceMoney(?Money $value): self
    {
        $this->instance->setPriceMoney($value);
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
     * Sets modifier list id field.
     *
     * @param string|null $value
     */
    public function modifierListId(?string $value): self
    {
        $this->instance->setModifierListId($value);
        return $this;
    }

    /**
     * Unsets modifier list id field.
     */
    public function unsetModifierListId(): self
    {
        $this->instance->unsetModifierListId();
        return $this;
    }

    /**
     * Sets location overrides field.
     *
     * @param ModifierLocationOverrides[]|null $value
     */
    public function locationOverrides(?array $value): self
    {
        $this->instance->setLocationOverrides($value);
        return $this;
    }

    /**
     * Unsets location overrides field.
     */
    public function unsetLocationOverrides(): self
    {
        $this->instance->unsetLocationOverrides();
        return $this;
    }

    /**
     * Sets image id field.
     *
     * @param string|null $value
     */
    public function imageId(?string $value): self
    {
        $this->instance->setImageId($value);
        return $this;
    }

    /**
     * Unsets image id field.
     */
    public function unsetImageId(): self
    {
        $this->instance->unsetImageId();
        return $this;
    }

    /**
     * Initializes a new Catalog Modifier object.
     */
    public function build(): CatalogModifier
    {
        return CoreHelper::clone($this->instance);
    }
}
