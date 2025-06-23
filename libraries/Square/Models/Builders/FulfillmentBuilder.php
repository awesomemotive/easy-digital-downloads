<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Fulfillment;
use EDD\Vendor\Square\Models\FulfillmentDeliveryDetails;
use EDD\Vendor\Square\Models\FulfillmentFulfillmentEntry;
use EDD\Vendor\Square\Models\FulfillmentPickupDetails;
use EDD\Vendor\Square\Models\FulfillmentShipmentDetails;

/**
 * Builder for model Fulfillment
 *
 * @see Fulfillment
 */
class FulfillmentBuilder
{
    /**
     * @var Fulfillment
     */
    private $instance;

    private function __construct(Fulfillment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Fulfillment Builder object.
     */
    public static function init(): self
    {
        return new self(new Fulfillment());
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
        return $this;
    }

    /**
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
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
     * Sets line item application field.
     *
     * @param string|null $value
     */
    public function lineItemApplication(?string $value): self
    {
        $this->instance->setLineItemApplication($value);
        return $this;
    }

    /**
     * Sets entries field.
     *
     * @param FulfillmentFulfillmentEntry[]|null $value
     */
    public function entries(?array $value): self
    {
        $this->instance->setEntries($value);
        return $this;
    }

    /**
     * Sets metadata field.
     *
     * @param array<string,string>|null $value
     */
    public function metadata(?array $value): self
    {
        $this->instance->setMetadata($value);
        return $this;
    }

    /**
     * Unsets metadata field.
     */
    public function unsetMetadata(): self
    {
        $this->instance->unsetMetadata();
        return $this;
    }

    /**
     * Sets pickup details field.
     *
     * @param FulfillmentPickupDetails|null $value
     */
    public function pickupDetails(?FulfillmentPickupDetails $value): self
    {
        $this->instance->setPickupDetails($value);
        return $this;
    }

    /**
     * Sets shipment details field.
     *
     * @param FulfillmentShipmentDetails|null $value
     */
    public function shipmentDetails(?FulfillmentShipmentDetails $value): self
    {
        $this->instance->setShipmentDetails($value);
        return $this;
    }

    /**
     * Sets delivery details field.
     *
     * @param FulfillmentDeliveryDetails|null $value
     */
    public function deliveryDetails(?FulfillmentDeliveryDetails $value): self
    {
        $this->instance->setDeliveryDetails($value);
        return $this;
    }

    /**
     * Initializes a new Fulfillment object.
     */
    public function build(): Fulfillment
    {
        return CoreHelper::clone($this->instance);
    }
}
