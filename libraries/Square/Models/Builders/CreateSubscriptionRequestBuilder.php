<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateSubscriptionRequest;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Phase;
use EDD\Vendor\Square\Models\SubscriptionSource;

/**
 * Builder for model CreateSubscriptionRequest
 *
 * @see CreateSubscriptionRequest
 */
class CreateSubscriptionRequestBuilder
{
    /**
     * @var CreateSubscriptionRequest
     */
    private $instance;

    private function __construct(CreateSubscriptionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Subscription Request Builder object.
     *
     * @param string $locationId
     * @param string $customerId
     */
    public static function init(string $locationId, string $customerId): self
    {
        return new self(new CreateSubscriptionRequest($locationId, $customerId));
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }

    /**
     * Sets plan variation id field.
     *
     * @param string|null $value
     */
    public function planVariationId(?string $value): self
    {
        $this->instance->setPlanVariationId($value);
        return $this;
    }

    /**
     * Sets start date field.
     *
     * @param string|null $value
     */
    public function startDate(?string $value): self
    {
        $this->instance->setStartDate($value);
        return $this;
    }

    /**
     * Sets canceled date field.
     *
     * @param string|null $value
     */
    public function canceledDate(?string $value): self
    {
        $this->instance->setCanceledDate($value);
        return $this;
    }

    /**
     * Sets tax percentage field.
     *
     * @param string|null $value
     */
    public function taxPercentage(?string $value): self
    {
        $this->instance->setTaxPercentage($value);
        return $this;
    }

    /**
     * Sets price override money field.
     *
     * @param Money|null $value
     */
    public function priceOverrideMoney(?Money $value): self
    {
        $this->instance->setPriceOverrideMoney($value);
        return $this;
    }

    /**
     * Sets card id field.
     *
     * @param string|null $value
     */
    public function cardId(?string $value): self
    {
        $this->instance->setCardId($value);
        return $this;
    }

    /**
     * Sets timezone field.
     *
     * @param string|null $value
     */
    public function timezone(?string $value): self
    {
        $this->instance->setTimezone($value);
        return $this;
    }

    /**
     * Sets source field.
     *
     * @param SubscriptionSource|null $value
     */
    public function source(?SubscriptionSource $value): self
    {
        $this->instance->setSource($value);
        return $this;
    }

    /**
     * Sets monthly billing anchor date field.
     *
     * @param int|null $value
     */
    public function monthlyBillingAnchorDate(?int $value): self
    {
        $this->instance->setMonthlyBillingAnchorDate($value);
        return $this;
    }

    /**
     * Sets phases field.
     *
     * @param Phase[]|null $value
     */
    public function phases(?array $value): self
    {
        $this->instance->setPhases($value);
        return $this;
    }

    /**
     * Initializes a new Create Subscription Request object.
     */
    public function build(): CreateSubscriptionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
