<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderSource;

/**
 * Builder for model OrderSource
 *
 * @see OrderSource
 */
class OrderSourceBuilder
{
    /**
     * @var OrderSource
     */
    private $instance;

    private function __construct(OrderSource $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Source Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderSource());
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
     * Initializes a new Order Source object.
     */
    public function build(): OrderSource
    {
        return CoreHelper::clone($this->instance);
    }
}
