<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ShiftSort;

/**
 * Builder for model ShiftSort
 *
 * @see ShiftSort
 */
class ShiftSortBuilder
{
    /**
     * @var ShiftSort
     */
    private $instance;

    private function __construct(ShiftSort $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Shift Sort Builder object.
     */
    public static function init(): self
    {
        return new self(new ShiftSort());
    }

    /**
     * Sets field field.
     *
     * @param string|null $value
     */
    public function field(?string $value): self
    {
        $this->instance->setField($value);
        return $this;
    }

    /**
     * Sets order field.
     *
     * @param string|null $value
     */
    public function order(?string $value): self
    {
        $this->instance->setOrder($value);
        return $this;
    }

    /**
     * Initializes a new Shift Sort object.
     */
    public function build(): ShiftSort
    {
        return CoreHelper::clone($this->instance);
    }
}
