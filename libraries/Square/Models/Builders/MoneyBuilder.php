<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model Money
 *
 * @see Money
 */
class MoneyBuilder
{
    /**
     * @var Money
     */
    private $instance;

    private function __construct(Money $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Money Builder object.
     */
    public static function init(): self
    {
        return new self(new Money());
    }

    /**
     * Sets amount field.
     *
     * @param int|null $value
     */
    public function amount(?int $value): self
    {
        $this->instance->setAmount($value);
        return $this;
    }

    /**
     * Unsets amount field.
     */
    public function unsetAmount(): self
    {
        $this->instance->unsetAmount();
        return $this;
    }

    /**
     * Sets currency field.
     *
     * @param string|null $value
     */
    public function currency(?string $value): self
    {
        $this->instance->setCurrency($value);
        return $this;
    }

    /**
     * Initializes a new Money object.
     */
    public function build(): Money
    {
        return CoreHelper::clone($this->instance);
    }
}
