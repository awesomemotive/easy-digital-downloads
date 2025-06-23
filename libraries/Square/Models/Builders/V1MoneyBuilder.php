<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\V1Money;

/**
 * Builder for model V1Money
 *
 * @see V1Money
 */
class V1MoneyBuilder
{
    /**
     * @var V1Money
     */
    private $instance;

    private function __construct(V1Money $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 Money Builder object.
     */
    public static function init(): self
    {
        return new self(new V1Money());
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
     * Sets currency code field.
     *
     * @param string|null $value
     */
    public function currencyCode(?string $value): self
    {
        $this->instance->setCurrencyCode($value);
        return $this;
    }

    /**
     * Initializes a new V1 Money object.
     */
    public function build(): V1Money
    {
        return CoreHelper::clone($this->instance);
    }
}
