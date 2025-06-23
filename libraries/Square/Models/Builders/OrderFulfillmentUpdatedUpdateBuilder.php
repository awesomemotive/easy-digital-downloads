<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderFulfillmentUpdatedUpdate;

/**
 * Builder for model OrderFulfillmentUpdatedUpdate
 *
 * @see OrderFulfillmentUpdatedUpdate
 */
class OrderFulfillmentUpdatedUpdateBuilder
{
    /**
     * @var OrderFulfillmentUpdatedUpdate
     */
    private $instance;

    private function __construct(OrderFulfillmentUpdatedUpdate $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Fulfillment Updated Update Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderFulfillmentUpdatedUpdate());
    }

    /**
     * Sets fulfillment uid field.
     *
     * @param string|null $value
     */
    public function fulfillmentUid(?string $value): self
    {
        $this->instance->setFulfillmentUid($value);
        return $this;
    }

    /**
     * Unsets fulfillment uid field.
     */
    public function unsetFulfillmentUid(): self
    {
        $this->instance->unsetFulfillmentUid();
        return $this;
    }

    /**
     * Sets old state field.
     *
     * @param string|null $value
     */
    public function oldState(?string $value): self
    {
        $this->instance->setOldState($value);
        return $this;
    }

    /**
     * Sets new state field.
     *
     * @param string|null $value
     */
    public function newState(?string $value): self
    {
        $this->instance->setNewState($value);
        return $this;
    }

    /**
     * Initializes a new Order Fulfillment Updated Update object.
     */
    public function build(): OrderFulfillmentUpdatedUpdate
    {
        return CoreHelper::clone($this->instance);
    }
}
