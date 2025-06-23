<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderFulfillmentUpdated;
use EDD\Vendor\Square\Models\OrderFulfillmentUpdatedObject;

/**
 * Builder for model OrderFulfillmentUpdatedObject
 *
 * @see OrderFulfillmentUpdatedObject
 */
class OrderFulfillmentUpdatedObjectBuilder
{
    /**
     * @var OrderFulfillmentUpdatedObject
     */
    private $instance;

    private function __construct(OrderFulfillmentUpdatedObject $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Fulfillment Updated Object Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderFulfillmentUpdatedObject());
    }

    /**
     * Sets order fulfillment updated field.
     *
     * @param OrderFulfillmentUpdated|null $value
     */
    public function orderFulfillmentUpdated(?OrderFulfillmentUpdated $value): self
    {
        $this->instance->setOrderFulfillmentUpdated($value);
        return $this;
    }

    /**
     * Initializes a new Order Fulfillment Updated Object object.
     */
    public function build(): OrderFulfillmentUpdatedObject
    {
        return CoreHelper::clone($this->instance);
    }
}
