<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderFulfillmentRecipient;
use EDD\Vendor\Square\Models\OrderFulfillmentShipmentDetails;

/**
 * Builder for model OrderFulfillmentShipmentDetails
 *
 * @see OrderFulfillmentShipmentDetails
 */
class OrderFulfillmentShipmentDetailsBuilder
{
    /**
     * @var OrderFulfillmentShipmentDetails
     */
    private $instance;

    private function __construct(OrderFulfillmentShipmentDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Fulfillment Shipment Details Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderFulfillmentShipmentDetails());
    }

    /**
     * Sets recipient field.
     *
     * @param OrderFulfillmentRecipient|null $value
     */
    public function recipient(?OrderFulfillmentRecipient $value): self
    {
        $this->instance->setRecipient($value);
        return $this;
    }

    /**
     * Sets carrier field.
     *
     * @param string|null $value
     */
    public function carrier(?string $value): self
    {
        $this->instance->setCarrier($value);
        return $this;
    }

    /**
     * Unsets carrier field.
     */
    public function unsetCarrier(): self
    {
        $this->instance->unsetCarrier();
        return $this;
    }

    /**
     * Sets shipping note field.
     *
     * @param string|null $value
     */
    public function shippingNote(?string $value): self
    {
        $this->instance->setShippingNote($value);
        return $this;
    }

    /**
     * Unsets shipping note field.
     */
    public function unsetShippingNote(): self
    {
        $this->instance->unsetShippingNote();
        return $this;
    }

    /**
     * Sets shipping type field.
     *
     * @param string|null $value
     */
    public function shippingType(?string $value): self
    {
        $this->instance->setShippingType($value);
        return $this;
    }

    /**
     * Unsets shipping type field.
     */
    public function unsetShippingType(): self
    {
        $this->instance->unsetShippingType();
        return $this;
    }

    /**
     * Sets tracking number field.
     *
     * @param string|null $value
     */
    public function trackingNumber(?string $value): self
    {
        $this->instance->setTrackingNumber($value);
        return $this;
    }

    /**
     * Unsets tracking number field.
     */
    public function unsetTrackingNumber(): self
    {
        $this->instance->unsetTrackingNumber();
        return $this;
    }

    /**
     * Sets tracking url field.
     *
     * @param string|null $value
     */
    public function trackingUrl(?string $value): self
    {
        $this->instance->setTrackingUrl($value);
        return $this;
    }

    /**
     * Unsets tracking url field.
     */
    public function unsetTrackingUrl(): self
    {
        $this->instance->unsetTrackingUrl();
        return $this;
    }

    /**
     * Sets placed at field.
     *
     * @param string|null $value
     */
    public function placedAt(?string $value): self
    {
        $this->instance->setPlacedAt($value);
        return $this;
    }

    /**
     * Sets in progress at field.
     *
     * @param string|null $value
     */
    public function inProgressAt(?string $value): self
    {
        $this->instance->setInProgressAt($value);
        return $this;
    }

    /**
     * Sets packaged at field.
     *
     * @param string|null $value
     */
    public function packagedAt(?string $value): self
    {
        $this->instance->setPackagedAt($value);
        return $this;
    }

    /**
     * Sets expected shipped at field.
     *
     * @param string|null $value
     */
    public function expectedShippedAt(?string $value): self
    {
        $this->instance->setExpectedShippedAt($value);
        return $this;
    }

    /**
     * Unsets expected shipped at field.
     */
    public function unsetExpectedShippedAt(): self
    {
        $this->instance->unsetExpectedShippedAt();
        return $this;
    }

    /**
     * Sets shipped at field.
     *
     * @param string|null $value
     */
    public function shippedAt(?string $value): self
    {
        $this->instance->setShippedAt($value);
        return $this;
    }

    /**
     * Sets canceled at field.
     *
     * @param string|null $value
     */
    public function canceledAt(?string $value): self
    {
        $this->instance->setCanceledAt($value);
        return $this;
    }

    /**
     * Unsets canceled at field.
     */
    public function unsetCanceledAt(): self
    {
        $this->instance->unsetCanceledAt();
        return $this;
    }

    /**
     * Sets cancel reason field.
     *
     * @param string|null $value
     */
    public function cancelReason(?string $value): self
    {
        $this->instance->setCancelReason($value);
        return $this;
    }

    /**
     * Unsets cancel reason field.
     */
    public function unsetCancelReason(): self
    {
        $this->instance->unsetCancelReason();
        return $this;
    }

    /**
     * Sets failed at field.
     *
     * @param string|null $value
     */
    public function failedAt(?string $value): self
    {
        $this->instance->setFailedAt($value);
        return $this;
    }

    /**
     * Sets failure reason field.
     *
     * @param string|null $value
     */
    public function failureReason(?string $value): self
    {
        $this->instance->setFailureReason($value);
        return $this;
    }

    /**
     * Unsets failure reason field.
     */
    public function unsetFailureReason(): self
    {
        $this->instance->unsetFailureReason();
        return $this;
    }

    /**
     * Initializes a new Order Fulfillment Shipment Details object.
     */
    public function build(): OrderFulfillmentShipmentDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
