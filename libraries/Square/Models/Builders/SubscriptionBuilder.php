<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Phase;
use EDD\Vendor\Square\Models\Subscription;
use EDD\Vendor\Square\Models\SubscriptionAction;
use EDD\Vendor\Square\Models\SubscriptionSource;

/**
 * Builder for model Subscription
 *
 * @see Subscription
 */
class SubscriptionBuilder
{
    /**
     * @var Subscription
     */
    private $instance;

    private function __construct(Subscription $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Subscription Builder object.
     */
    public static function init(): self
    {
        return new self(new Subscription());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
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
     * Sets customer id field.
     *
     * @param string|null $value
     */
    public function customerId(?string $value): self
    {
        $this->instance->setCustomerId($value);
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
     * Unsets canceled date field.
     */
    public function unsetCanceledDate(): self
    {
        $this->instance->unsetCanceledDate();
        return $this;
    }

    /**
     * Sets charged through date field.
     *
     * @param string|null $value
     */
    public function chargedThroughDate(?string $value): self
    {
        $this->instance->setChargedThroughDate($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
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
     * Unsets tax percentage field.
     */
    public function unsetTaxPercentage(): self
    {
        $this->instance->unsetTaxPercentage();
        return $this;
    }

    /**
     * Sets invoice ids field.
     *
     * @param string[]|null $value
     */
    public function invoiceIds(?array $value): self
    {
        $this->instance->setInvoiceIds($value);
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
     * Unsets card id field.
     */
    public function unsetCardId(): self
    {
        $this->instance->unsetCardId();
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
     * Sets actions field.
     *
     * @param SubscriptionAction[]|null $value
     */
    public function actions(?array $value): self
    {
        $this->instance->setActions($value);
        return $this;
    }

    /**
     * Unsets actions field.
     */
    public function unsetActions(): self
    {
        $this->instance->unsetActions();
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
     * Initializes a new Subscription object.
     */
    public function build(): Subscription
    {
        return CoreHelper::clone($this->instance);
    }
}
