<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderCreated;
use EDD\Vendor\Square\Models\OrderCreatedObject;

/**
 * Builder for model OrderCreatedObject
 *
 * @see OrderCreatedObject
 */
class OrderCreatedObjectBuilder
{
    /**
     * @var OrderCreatedObject
     */
    private $instance;

    private function __construct(OrderCreatedObject $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Created Object Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderCreatedObject());
    }

    /**
     * Sets order created field.
     *
     * @param OrderCreated|null $value
     */
    public function orderCreated(?OrderCreated $value): self
    {
        $this->instance->setOrderCreated($value);
        return $this;
    }

    /**
     * Initializes a new Order Created Object object.
     */
    public function build(): OrderCreatedObject
    {
        return CoreHelper::clone($this->instance);
    }
}
