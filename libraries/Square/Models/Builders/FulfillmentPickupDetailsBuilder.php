<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\FulfillmentPickupDetails;
use EDD\Vendor\Square\Models\FulfillmentPickupDetailsCurbsidePickupDetails;
use EDD\Vendor\Square\Models\FulfillmentRecipient;

/**
 * Builder for model FulfillmentPickupDetails
 *
 * @see FulfillmentPickupDetails
 */
class FulfillmentPickupDetailsBuilder
{
    /**
     * @var FulfillmentPickupDetails
     */
    private $instance;

    private function __construct(FulfillmentPickupDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Fulfillment Pickup Details Builder object.
     */
    public static function init(): self
    {
        return new self(new FulfillmentPickupDetails());
    }

    /**
     * Sets recipient field.
     *
     * @param FulfillmentRecipient|null $value
     */
    public function recipient(?FulfillmentRecipient $value): self
    {
        $this->instance->setRecipient($value);
        return $this;
    }

    /**
     * Sets expires at field.
     *
     * @param string|null $value
     */
    public function expiresAt(?string $value): self
    {
        $this->instance->setExpiresAt($value);
        return $this;
    }

    /**
     * Unsets expires at field.
     */
    public function unsetExpiresAt(): self
    {
        $this->instance->unsetExpiresAt();
        return $this;
    }

    /**
     * Sets auto complete duration field.
     *
     * @param string|null $value
     */
    public function autoCompleteDuration(?string $value): self
    {
        $this->instance->setAutoCompleteDuration($value);
        return $this;
    }

    /**
     * Unsets auto complete duration field.
     */
    public function unsetAutoCompleteDuration(): self
    {
        $this->instance->unsetAutoCompleteDuration();
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
     * Sets pickup at field.
     *
     * @param string|null $value
     */
    public function pickupAt(?string $value): self
    {
        $this->instance->setPickupAt($value);
        return $this;
    }

    /**
     * Unsets pickup at field.
     */
    public function unsetPickupAt(): self
    {
        $this->instance->unsetPickupAt();
        return $this;
    }

    /**
     * Sets pickup window duration field.
     *
     * @param string|null $value
     */
    public function pickupWindowDuration(?string $value): self
    {
        $this->instance->setPickupWindowDuration($value);
        return $this;
    }

    /**
     * Unsets pickup window duration field.
     */
    public function unsetPickupWindowDuration(): self
    {
        $this->instance->unsetPickupWindowDuration();
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
     * Sets accepted at field.
     *
     * @param string|null $value
     */
    public function acceptedAt(?string $value): self
    {
        $this->instance->setAcceptedAt($value);
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
     * Sets expired at field.
     *
     * @param string|null $value
     */
    public function expiredAt(?string $value): self
    {
        $this->instance->setExpiredAt($value);
        return $this;
    }

    /**
     * Sets picked up at field.
     *
     * @param string|null $value
     */
    public function pickedUpAt(?string $value): self
    {
        $this->instance->setPickedUpAt($value);
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
     * Sets is curbside pickup field.
     *
     * @param bool|null $value
     */
    public function isCurbsidePickup(?bool $value): self
    {
        $this->instance->setIsCurbsidePickup($value);
        return $this;
    }

    /**
     * Unsets is curbside pickup field.
     */
    public function unsetIsCurbsidePickup(): self
    {
        $this->instance->unsetIsCurbsidePickup();
        return $this;
    }

    /**
     * Sets curbside pickup details field.
     *
     * @param FulfillmentPickupDetailsCurbsidePickupDetails|null $value
     */
    public function curbsidePickupDetails(?FulfillmentPickupDetailsCurbsidePickupDetails $value): self
    {
        $this->instance->setCurbsidePickupDetails($value);
        return $this;
    }

    /**
     * Initializes a new Fulfillment Pickup Details object.
     */
    public function build(): FulfillmentPickupDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
