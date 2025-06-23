<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQuickAmount;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CatalogQuickAmount
 *
 * @see CatalogQuickAmount
 */
class CatalogQuickAmountBuilder
{
    /**
     * @var CatalogQuickAmount
     */
    private $instance;

    private function __construct(CatalogQuickAmount $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Quick Amount Builder object.
     *
     * @param string $type
     * @param Money $amount
     */
    public static function init(string $type, Money $amount): self
    {
        return new self(new CatalogQuickAmount($type, $amount));
    }

    /**
     * Sets score field.
     *
     * @param int|null $value
     */
    public function score(?int $value): self
    {
        $this->instance->setScore($value);
        return $this;
    }

    /**
     * Unsets score field.
     */
    public function unsetScore(): self
    {
        $this->instance->unsetScore();
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
     * Initializes a new Catalog Quick Amount object.
     */
    public function build(): CatalogQuickAmount
    {
        return CoreHelper::clone($this->instance);
    }
}
