<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderLineItemAppliedDiscount;
use EDD\Vendor\Square\Models\OrderLineItemAppliedServiceCharge;
use EDD\Vendor\Square\Models\OrderLineItemAppliedTax;
use EDD\Vendor\Square\Models\OrderQuantityUnit;
use EDD\Vendor\Square\Models\OrderReturnLineItem;
use EDD\Vendor\Square\Models\OrderReturnLineItemModifier;

/**
 * Builder for model OrderReturnLineItem
 *
 * @see OrderReturnLineItem
 */
class OrderReturnLineItemBuilder
{
    /**
     * @var OrderReturnLineItem
     */
    private $instance;

    private function __construct(OrderReturnLineItem $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Order Return Line Item Builder object.
     *
     * @param string $quantity
     */
    public static function init(string $quantity): self
    {
        return new self(new OrderReturnLineItem($quantity));
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
     * Sets source line item uid field.
     *
     * @param string|null $value
     */
    public function sourceLineItemUid(?string $value): self
    {
        $this->instance->setSourceLineItemUid($value);
        return $this;
    }

    /**
     * Unsets source line item uid field.
     */
    public function unsetSourceLineItemUid(): self
    {
        $this->instance->unsetSourceLineItemUid();
        return $this;
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets quantity unit field.
     *
     * @param OrderQuantityUnit|null $value
     */
    public function quantityUnit(?OrderQuantityUnit $value): self
    {
        $this->instance->setQuantityUnit($value);
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
     * Sets catalog object id field.
     *
     * @param string|null $value
     */
    public function catalogObjectId(?string $value): self
    {
        $this->instance->setCatalogObjectId($value);
        return $this;
    }

    /**
     * Unsets catalog object id field.
     */
    public function unsetCatalogObjectId(): self
    {
        $this->instance->unsetCatalogObjectId();
        return $this;
    }

    /**
     * Sets catalog version field.
     *
     * @param int|null $value
     */
    public function catalogVersion(?int $value): self
    {
        $this->instance->setCatalogVersion($value);
        return $this;
    }

    /**
     * Unsets catalog version field.
     */
    public function unsetCatalogVersion(): self
    {
        $this->instance->unsetCatalogVersion();
        return $this;
    }

    /**
     * Sets variation name field.
     *
     * @param string|null $value
     */
    public function variationName(?string $value): self
    {
        $this->instance->setVariationName($value);
        return $this;
    }

    /**
     * Unsets variation name field.
     */
    public function unsetVariationName(): self
    {
        $this->instance->unsetVariationName();
        return $this;
    }

    /**
     * Sets item type field.
     *
     * @param string|null $value
     */
    public function itemType(?string $value): self
    {
        $this->instance->setItemType($value);
        return $this;
    }

    /**
     * Sets return modifiers field.
     *
     * @param OrderReturnLineItemModifier[]|null $value
     */
    public function returnModifiers(?array $value): self
    {
        $this->instance->setReturnModifiers($value);
        return $this;
    }

    /**
     * Unsets return modifiers field.
     */
    public function unsetReturnModifiers(): self
    {
        $this->instance->unsetReturnModifiers();
        return $this;
    }

    /**
     * Sets applied taxes field.
     *
     * @param OrderLineItemAppliedTax[]|null $value
     */
    public function appliedTaxes(?array $value): self
    {
        $this->instance->setAppliedTaxes($value);
        return $this;
    }

    /**
     * Unsets applied taxes field.
     */
    public function unsetAppliedTaxes(): self
    {
        $this->instance->unsetAppliedTaxes();
        return $this;
    }

    /**
     * Sets applied discounts field.
     *
     * @param OrderLineItemAppliedDiscount[]|null $value
     */
    public function appliedDiscounts(?array $value): self
    {
        $this->instance->setAppliedDiscounts($value);
        return $this;
    }

    /**
     * Unsets applied discounts field.
     */
    public function unsetAppliedDiscounts(): self
    {
        $this->instance->unsetAppliedDiscounts();
        return $this;
    }

    /**
     * Sets base price money field.
     *
     * @param Money|null $value
     */
    public function basePriceMoney(?Money $value): self
    {
        $this->instance->setBasePriceMoney($value);
        return $this;
    }

    /**
     * Sets variation total price money field.
     *
     * @param Money|null $value
     */
    public function variationTotalPriceMoney(?Money $value): self
    {
        $this->instance->setVariationTotalPriceMoney($value);
        return $this;
    }

    /**
     * Sets gross return money field.
     *
     * @param Money|null $value
     */
    public function grossReturnMoney(?Money $value): self
    {
        $this->instance->setGrossReturnMoney($value);
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
     * Sets applied service charges field.
     *
     * @param OrderLineItemAppliedServiceCharge[]|null $value
     */
    public function appliedServiceCharges(?array $value): self
    {
        $this->instance->setAppliedServiceCharges($value);
        return $this;
    }

    /**
     * Unsets applied service charges field.
     */
    public function unsetAppliedServiceCharges(): self
    {
        $this->instance->unsetAppliedServiceCharges();
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
     * Initializes a new Order Return Line Item object.
     */
    public function build(): OrderReturnLineItem
    {
        return CoreHelper::clone($this->instance);
    }
}
