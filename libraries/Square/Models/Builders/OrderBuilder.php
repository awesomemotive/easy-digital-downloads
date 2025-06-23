<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Fulfillment;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\Order;
use EDD\Vendor\Square\Models\OrderLineItem;
use EDD\Vendor\Square\Models\OrderLineItemDiscount;
use EDD\Vendor\Square\Models\OrderLineItemTax;
use EDD\Vendor\Square\Models\OrderMoneyAmounts;
use EDD\Vendor\Square\Models\OrderPricingOptions;
use EDD\Vendor\Square\Models\OrderReturn;
use EDD\Vendor\Square\Models\OrderReward;
use EDD\Vendor\Square\Models\OrderRoundingAdjustment;
use EDD\Vendor\Square\Models\OrderServiceCharge;
use EDD\Vendor\Square\Models\OrderSource;
use EDD\Vendor\Square\Models\Refund;
use EDD\Vendor\Square\Models\Tender;

/**
 * Builder for model Order
 *
 * @see Order
 */
class OrderBuilder
{
    /**
     * @var Order
     */
    private $instance;

    private function __construct(Order $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Builder object.
     *
     * @param string $locationId
     */
    public static function init(string $locationId): self
    {
        return new self(new Order($locationId));
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
     * Sets reference id field.
     *
     * @param string|null $value
     */
    public function referenceId(?string $value): self
    {
        $this->instance->setReferenceId($value);
        return $this;
    }

    /**
     * Unsets reference id field.
     */
    public function unsetReferenceId(): self
    {
        $this->instance->unsetReferenceId();
        return $this;
    }

    /**
     * Sets source field.
     *
     * @param OrderSource|null $value
     */
    public function source(?OrderSource $value): self
    {
        $this->instance->setSource($value);
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
     * Unsets customer id field.
     */
    public function unsetCustomerId(): self
    {
        $this->instance->unsetCustomerId();
        return $this;
    }

    /**
     * Sets line items field.
     *
     * @param OrderLineItem[]|null $value
     */
    public function lineItems(?array $value): self
    {
        $this->instance->setLineItems($value);
        return $this;
    }

    /**
     * Unsets line items field.
     */
    public function unsetLineItems(): self
    {
        $this->instance->unsetLineItems();
        return $this;
    }

    /**
     * Sets taxes field.
     *
     * @param OrderLineItemTax[]|null $value
     */
    public function taxes(?array $value): self
    {
        $this->instance->setTaxes($value);
        return $this;
    }

    /**
     * Unsets taxes field.
     */
    public function unsetTaxes(): self
    {
        $this->instance->unsetTaxes();
        return $this;
    }

    /**
     * Sets discounts field.
     *
     * @param OrderLineItemDiscount[]|null $value
     */
    public function discounts(?array $value): self
    {
        $this->instance->setDiscounts($value);
        return $this;
    }

    /**
     * Unsets discounts field.
     */
    public function unsetDiscounts(): self
    {
        $this->instance->unsetDiscounts();
        return $this;
    }

    /**
     * Sets service charges field.
     *
     * @param OrderServiceCharge[]|null $value
     */
    public function serviceCharges(?array $value): self
    {
        $this->instance->setServiceCharges($value);
        return $this;
    }

    /**
     * Unsets service charges field.
     */
    public function unsetServiceCharges(): self
    {
        $this->instance->unsetServiceCharges();
        return $this;
    }

    /**
     * Sets fulfillments field.
     *
     * @param Fulfillment[]|null $value
     */
    public function fulfillments(?array $value): self
    {
        $this->instance->setFulfillments($value);
        return $this;
    }

    /**
     * Unsets fulfillments field.
     */
    public function unsetFulfillments(): self
    {
        $this->instance->unsetFulfillments();
        return $this;
    }

    /**
     * Sets returns field.
     *
     * @param OrderReturn[]|null $value
     */
    public function returns(?array $value): self
    {
        $this->instance->setReturns($value);
        return $this;
    }

    /**
     * Sets return amounts field.
     *
     * @param OrderMoneyAmounts|null $value
     */
    public function returnAmounts(?OrderMoneyAmounts $value): self
    {
        $this->instance->setReturnAmounts($value);
        return $this;
    }

    /**
     * Sets net amounts field.
     *
     * @param OrderMoneyAmounts|null $value
     */
    public function netAmounts(?OrderMoneyAmounts $value): self
    {
        $this->instance->setNetAmounts($value);
        return $this;
    }

    /**
     * Sets rounding adjustment field.
     *
     * @param OrderRoundingAdjustment|null $value
     */
    public function roundingAdjustment(?OrderRoundingAdjustment $value): self
    {
        $this->instance->setRoundingAdjustment($value);
        return $this;
    }

    /**
     * Sets tenders field.
     *
     * @param Tender[]|null $value
     */
    public function tenders(?array $value): self
    {
        $this->instance->setTenders($value);
        return $this;
    }

    /**
     * Sets refunds field.
     *
     * @param Refund[]|null $value
     */
    public function refunds(?array $value): self
    {
        $this->instance->setRefunds($value);
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
     * Sets closed at field.
     *
     * @param string|null $value
     */
    public function closedAt(?string $value): self
    {
        $this->instance->setClosedAt($value);
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
     * Sets total money field.
     *
     * @param Money|null $value
     */
    public function totalMoney(?Money $value): self
    {
        $this->instance->setTotalMoney($value);
        return $this;
    }

    /**
     * Sets total tax money field.
     *
     * @param Money|null $value
     */
    public function totalTaxMoney(?Money $value): self
    {
        $this->instance->setTotalTaxMoney($value);
        return $this;
    }

    /**
     * Sets total discount money field.
     *
     * @param Money|null $value
     */
    public function totalDiscountMoney(?Money $value): self
    {
        $this->instance->setTotalDiscountMoney($value);
        return $this;
    }

    /**
     * Sets total tip money field.
     *
     * @param Money|null $value
     */
    public function totalTipMoney(?Money $value): self
    {
        $this->instance->setTotalTipMoney($value);
        return $this;
    }

    /**
     * Sets total service charge money field.
     *
     * @param Money|null $value
     */
    public function totalServiceChargeMoney(?Money $value): self
    {
        $this->instance->setTotalServiceChargeMoney($value);
        return $this;
    }

    /**
     * Sets ticket name field.
     *
     * @param string|null $value
     */
    public function ticketName(?string $value): self
    {
        $this->instance->setTicketName($value);
        return $this;
    }

    /**
     * Unsets ticket name field.
     */
    public function unsetTicketName(): self
    {
        $this->instance->unsetTicketName();
        return $this;
    }

    /**
     * Sets pricing options field.
     *
     * @param OrderPricingOptions|null $value
     */
    public function pricingOptions(?OrderPricingOptions $value): self
    {
        $this->instance->setPricingOptions($value);
        return $this;
    }

    /**
     * Sets rewards field.
     *
     * @param OrderReward[]|null $value
     */
    public function rewards(?array $value): self
    {
        $this->instance->setRewards($value);
        return $this;
    }

    /**
     * Sets net amount due money field.
     *
     * @param Money|null $value
     */
    public function netAmountDueMoney(?Money $value): self
    {
        $this->instance->setNetAmountDueMoney($value);
        return $this;
    }

    /**
     * Initializes a new Order object.
     */
    public function build(): Order
    {
        return CoreHelper::clone($this->instance);
    }
}
