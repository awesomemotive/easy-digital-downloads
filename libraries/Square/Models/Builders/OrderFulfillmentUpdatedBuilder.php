<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderFulfillmentUpdated;
use EDD\Vendor\Square\Models\OrderFulfillmentUpdatedUpdate;

/**
 * Builder for model OrderFulfillmentUpdated
 *
 * @see OrderFulfillmentUpdated
 */
class OrderFulfillmentUpdatedBuilder
{
    /**
     * @var OrderFulfillmentUpdated
     */
    private $instance;

    private function __construct(OrderFulfillmentUpdated $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Fulfillment Updated Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderFulfillmentUpdated());
    }

    /**
     * Sets order id field.
     *
     * @param string|null $value
     */
    public function orderId(?string $value): self
    {
        $this->instance->setOrderId($value);
        return $this;
    }

    /**
     * Unsets order id field.
     */
    public function unsetOrderId(): self
    {
        $this->instance->unsetOrderId();
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets state field.
     *
     * @param string|null $value
     */
    public function state(?string $value): self
    {
        $this->instance->setState($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets fulfillment update field.
     *
     * @param OrderFulfillmentUpdatedUpdate[]|null $value
     */
    public function fulfillmentUpdate(?array $value): self
    {
        $this->instance->setFulfillmentUpdate($value);
        return $this;
    }

    /**
     * Unsets fulfillment update field.
     */
    public function unsetFulfillmentUpdate(): self
    {
        $this->instance->unsetFulfillmentUpdate();
        return $this;
    }

    /**
     * Initializes a new Order Fulfillment Updated object.
     */
    public function build(): OrderFulfillmentUpdated
    {
        return CoreHelper::clone($this->instance);
    }
}
