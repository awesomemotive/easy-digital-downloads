<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\OrderFulfillmentDeliveryDetails;
use EDD\Vendor\Square\Models\OrderFulfillmentRecipient;

/**
 * Builder for model OrderFulfillmentDeliveryDetails
 *
 * @see OrderFulfillmentDeliveryDetails
 */
class OrderFulfillmentDeliveryDetailsBuilder
{
    /**
     * @var OrderFulfillmentDeliveryDetails
     */
    private $instance;

    private function __construct(OrderFulfillmentDeliveryDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Fulfillment Delivery Details Builder object.
     */
    public static function init(): self
    {
        return new self(new OrderFulfillmentDeliveryDetails());
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
     * Sets schedule type field.
     *
     * @param string|null $value
     */
    public function scheduleType(?string $value): self
    {
        $this->instance->setScheduleType($value);
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
     * Sets deliver at field.
     *
     * @param string|null $value
     */
    public function deliverAt(?string $value): self
    {
        $this->instance->setDeliverAt($value);
        return $this;
    }

    /**
     * Unsets deliver at field.
     */
    public function unsetDeliverAt(): self
    {
        $this->instance->unsetDeliverAt();
        return $this;
    }

    /**
     * Sets prep time duration field.
     *
     * @param string|null $value
     */
    public function prepTimeDuration(?string $value): self
    {
        $this->instance->setPrepTimeDuration($value);
        return $this;
    }

    /**
     * Unsets prep time duration field.
     */
    public function unsetPrepTimeDuration(): self
    {
        $this->instance->unsetPrepTimeDuration();
        return $this;
    }

    /**
     * Sets delivery window duration field.
     *
     * @param string|null $value
     */
    public function deliveryWindowDuration(?string $value): self
    {
        $this->instance->setDeliveryWindowDuration($value);
        return $this;
    }

    /**
     * Unsets delivery window duration field.
     */
    public function unsetDeliveryWindowDuration(): self
    {
        $this->instance->unsetDeliveryWindowDuration();
        return $this;
    }

    /**
     * Sets note field.
     *
     * @param string|null $value
     */
    public function note(?string $value): self
    {
        $this->instance->setNote($value);
        return $this;
    }

    /**
     * Unsets note field.
     */
    public function unsetNote(): self
    {
        $this->instance->unsetNote();
        return $this;
    }

    /**
     * Sets completed at field.
     *
     * @param string|null $value
     */
    public function completedAt(?string $value): self
    {
        $this->instance->setCompletedAt($value);
        return $this;
    }

    /**
     * Unsets completed at field.
     */
    public function unsetCompletedAt(): self
    {
        $this->instance->unsetCompletedAt();
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
     * Sets rejected at field.
     *
     * @param string|null $value
     */
    public function rejectedAt(?string $value): self
    {
        $this->instance->setRejectedAt($value);
        return $this;
    }

    /**
     * Sets ready at field.
     *
     * @param string|null $value
     */
    public function readyAt(?string $value): self
    {
        $this->instance->setReadyAt($value);
        return $this;
    }

    /**
     * Sets delivered at field.
     *
     * @param string|null $value
     */
    public function deliveredAt(?string $value): self
    {
        $this->instance->setDeliveredAt($value);
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
     * Sets courier pickup at field.
     *
     * @param string|null $value
     */
    public function courierPickupAt(?string $value): self
    {
        $this->instance->setCourierPickupAt($value);
        return $this;
    }

    /**
     * Unsets courier pickup at field.
     */
    public function unsetCourierPickupAt(): self
    {
        $this->instance->unsetCourierPickupAt();
        return $this;
    }

    /**
     * Sets courier pickup window duration field.
     *
     * @param string|null $value
     */
    public function courierPickupWindowDuration(?string $value): self
    {
        $this->instance->setCourierPickupWindowDuration($value);
        return $this;
    }

    /**
     * Unsets courier pickup window duration field.
     */
    public function unsetCourierPickupWindowDuration(): self
    {
        $this->instance->unsetCourierPickupWindowDuration();
        return $this;
    }

    /**
     * Sets is no contact delivery field.
     *
     * @param bool|null $value
     */
    public function isNoContactDelivery(?bool $value): self
    {
        $this->instance->setIsNoContactDelivery($value);
        return $this;
    }

    /**
     * Unsets is no contact delivery field.
     */
    public function unsetIsNoContactDelivery(): self
    {
        $this->instance->unsetIsNoContactDelivery();
        return $this;
    }

    /**
     * Sets dropoff notes field.
     *
     * @param string|null $value
     */
    public function dropoffNotes(?string $value): self
    {
        $this->instance->setDropoffNotes($value);
        return $this;
    }

    /**
     * Unsets dropoff notes field.
     */
    public function unsetDropoffNotes(): self
    {
        $this->instance->unsetDropoffNotes();
        return $this;
    }

    /**
     * Sets courier provider name field.
     *
     * @param string|null $value
     */
    public function courierProviderName(?string $value): self
    {
        $this->instance->setCourierProviderName($value);
        return $this;
    }

    /**
     * Unsets courier provider name field.
     */
    public function unsetCourierProviderName(): self
    {
        $this->instance->unsetCourierProviderName();
        return $this;
    }

    /**
     * Sets courier support phone number field.
     *
     * @param string|null $value
     */
    public function courierSupportPhoneNumber(?string $value): self
    {
        $this->instance->setCourierSupportPhoneNumber($value);
        return $this;
    }

    /**
     * Unsets courier support phone number field.
     */
    public function unsetCourierSupportPhoneNumber(): self
    {
        $this->instance->unsetCourierSupportPhoneNumber();
        return $this;
    }

    /**
     * Sets square delivery id field.
     *
     * @param string|null $value
     */
    public function squareDeliveryId(?string $value): self
    {
        $this->instance->setSquareDeliveryId($value);
        return $this;
    }

    /**
     * Unsets square delivery id field.
     */
    public function unsetSquareDeliveryId(): self
    {
        $this->instance->unsetSquareDeliveryId();
        return $this;
    }

    /**
     * Sets external delivery id field.
     *
     * @param string|null $value
     */
    public function externalDeliveryId(?string $value): self
    {
        $this->instance->setExternalDeliveryId($value);
        return $this;
    }

    /**
     * Unsets external delivery id field.
     */
    public function unsetExternalDeliveryId(): self
    {
        $this->instance->unsetExternalDeliveryId();
        return $this;
    }

    /**
     * Sets managed delivery field.
     *
     * @param bool|null $value
     */
    public function managedDelivery(?bool $value): self
    {
        $this->instance->setManagedDelivery($value);
        return $this;
    }

    /**
     * Unsets managed delivery field.
     */
    public function unsetManagedDelivery(): self
    {
        $this->instance->unsetManagedDelivery();
        return $this;
    }

    /**
     * Initializes a new Order Fulfillment Delivery Details object.
     */
    public function build(): OrderFulfillmentDeliveryDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
