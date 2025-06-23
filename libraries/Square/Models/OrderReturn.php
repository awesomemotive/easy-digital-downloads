<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The set of line items, service charges, taxes, discounts, tips, and other items being returned in an
 * order.
 */
class OrderReturn implements \JsonSerializable
{
    /**
     * @var array
     */
    private $uid = [];

    /**
     * @var array
     */
    private $sourceOrderId = [];

    /**
     * @var array
     */
    private $returnLineItems = [];

    /**
     * @var array
     */
    private $returnServiceCharges = [];

    /**
     * @var OrderReturnTax[]|null
     */
    private $returnTaxes;

    /**
     * @var OrderReturnDiscount[]|null
     */
    private $returnDiscounts;

    /**
     * @var array
     */
    private $returnTips = [];

    /**
     * @var OrderRoundingAdjustment|null
     */
    private $roundingAdjustment;

    /**
     * @var OrderMoneyAmounts|null
     */
    private $returnAmounts;

    /**
     * Returns Uid.
     * A unique ID that identifies the return only within this order.
     */
    public function getUid(): ?string
    {
        if (count($this->uid) == 0) {
            return null;
        }
        return $this->uid['value'];
    }

    /**
     * Sets Uid.
     * A unique ID that identifies the return only within this order.
     *
     * @maps uid
     */
    public function setUid(?string $uid): void
    {
        $this->uid['value'] = $uid;
    }

    /**
     * Unsets Uid.
     * A unique ID that identifies the return only within this order.
     */
    public function unsetUid(): void
    {
        $this->uid = [];
    }

    /**
     * Returns Source Order Id.
     * An order that contains the original sale of these return line items. This is unset
     * for unlinked returns.
     */
    public function getSourceOrderId(): ?string
    {
        if (count($this->sourceOrderId) == 0) {
            return null;
        }
        return $this->sourceOrderId['value'];
    }

    /**
     * Sets Source Order Id.
     * An order that contains the original sale of these return line items. This is unset
     * for unlinked returns.
     *
     * @maps source_order_id
     */
    public function setSourceOrderId(?string $sourceOrderId): void
    {
        $this->sourceOrderId['value'] = $sourceOrderId;
    }

    /**
     * Unsets Source Order Id.
     * An order that contains the original sale of these return line items. This is unset
     * for unlinked returns.
     */
    public function unsetSourceOrderId(): void
    {
        $this->sourceOrderId = [];
    }

    /**
     * Returns Return Line Items.
     * A collection of line items that are being returned.
     *
     * @return OrderReturnLineItem[]|null
     */
    public function getReturnLineItems(): ?array
    {
        if (count($this->returnLineItems) == 0) {
            return null;
        }
        return $this->returnLineItems['value'];
    }

    /**
     * Sets Return Line Items.
     * A collection of line items that are being returned.
     *
     * @maps return_line_items
     *
     * @param OrderReturnLineItem[]|null $returnLineItems
     */
    public function setReturnLineItems(?array $returnLineItems): void
    {
        $this->returnLineItems['value'] = $returnLineItems;
    }

    /**
     * Unsets Return Line Items.
     * A collection of line items that are being returned.
     */
    public function unsetReturnLineItems(): void
    {
        $this->returnLineItems = [];
    }

    /**
     * Returns Return Service Charges.
     * A collection of service charges that are being returned.
     *
     * @return OrderReturnServiceCharge[]|null
     */
    public function getReturnServiceCharges(): ?array
    {
        if (count($this->returnServiceCharges) == 0) {
            return null;
        }
        return $this->returnServiceCharges['value'];
    }

    /**
     * Sets Return Service Charges.
     * A collection of service charges that are being returned.
     *
     * @maps return_service_charges
     *
     * @param OrderReturnServiceCharge[]|null $returnServiceCharges
     */
    public function setReturnServiceCharges(?array $returnServiceCharges): void
    {
        $this->returnServiceCharges['value'] = $returnServiceCharges;
    }

    /**
     * Unsets Return Service Charges.
     * A collection of service charges that are being returned.
     */
    public function unsetReturnServiceCharges(): void
    {
        $this->returnServiceCharges = [];
    }

    /**
     * Returns Return Taxes.
     * A collection of references to taxes being returned for an order, including the total
     * applied tax amount to be returned. The taxes must reference a top-level tax ID from the source
     * order.
     *
     * @return OrderReturnTax[]|null
     */
    public function getReturnTaxes(): ?array
    {
        return $this->returnTaxes;
    }

    /**
     * Sets Return Taxes.
     * A collection of references to taxes being returned for an order, including the total
     * applied tax amount to be returned. The taxes must reference a top-level tax ID from the source
     * order.
     *
     * @maps return_taxes
     *
     * @param OrderReturnTax[]|null $returnTaxes
     */
    public function setReturnTaxes(?array $returnTaxes): void
    {
        $this->returnTaxes = $returnTaxes;
    }

    /**
     * Returns Return Discounts.
     * A collection of references to discounts being returned for an order, including the total
     * applied discount amount to be returned. The discounts must reference a top-level discount ID
     * from the source order.
     *
     * @return OrderReturnDiscount[]|null
     */
    public function getReturnDiscounts(): ?array
    {
        return $this->returnDiscounts;
    }

    /**
     * Sets Return Discounts.
     * A collection of references to discounts being returned for an order, including the total
     * applied discount amount to be returned. The discounts must reference a top-level discount ID
     * from the source order.
     *
     * @maps return_discounts
     *
     * @param OrderReturnDiscount[]|null $returnDiscounts
     */
    public function setReturnDiscounts(?array $returnDiscounts): void
    {
        $this->returnDiscounts = $returnDiscounts;
    }

    /**
     * Returns Return Tips.
     * A collection of references to tips being returned for an order.
     *
     * @return OrderReturnTip[]|null
     */
    public function getReturnTips(): ?array
    {
        if (count($this->returnTips) == 0) {
            return null;
        }
        return $this->returnTips['value'];
    }

    /**
     * Sets Return Tips.
     * A collection of references to tips being returned for an order.
     *
     * @maps return_tips
     *
     * @param OrderReturnTip[]|null $returnTips
     */
    public function setReturnTips(?array $returnTips): void
    {
        $this->returnTips['value'] = $returnTips;
    }

    /**
     * Unsets Return Tips.
     * A collection of references to tips being returned for an order.
     */
    public function unsetReturnTips(): void
    {
        $this->returnTips = [];
    }

    /**
     * Returns Rounding Adjustment.
     * A rounding adjustment of the money being returned. Commonly used to apply cash rounding
     * when the minimum unit of the account is smaller than the lowest physical denomination of the
     * currency.
     */
    public function getRoundingAdjustment(): ?OrderRoundingAdjustment
    {
        return $this->roundingAdjustment;
    }

    /**
     * Sets Rounding Adjustment.
     * A rounding adjustment of the money being returned. Commonly used to apply cash rounding
     * when the minimum unit of the account is smaller than the lowest physical denomination of the
     * currency.
     *
     * @maps rounding_adjustment
     */
    public function setRoundingAdjustment(?OrderRoundingAdjustment $roundingAdjustment): void
    {
        $this->roundingAdjustment = $roundingAdjustment;
    }

    /**
     * Returns Return Amounts.
     * A collection of various money amounts.
     */
    public function getReturnAmounts(): ?OrderMoneyAmounts
    {
        return $this->returnAmounts;
    }

    /**
     * Sets Return Amounts.
     * A collection of various money amounts.
     *
     * @maps return_amounts
     */
    public function setReturnAmounts(?OrderMoneyAmounts $returnAmounts): void
    {
        $this->returnAmounts = $returnAmounts;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (!empty($this->uid)) {
            $json['uid']                    = $this->uid['value'];
        }
        if (!empty($this->sourceOrderId)) {
            $json['source_order_id']        = $this->sourceOrderId['value'];
        }
        if (!empty($this->returnLineItems)) {
            $json['return_line_items']      = $this->returnLineItems['value'];
        }
        if (!empty($this->returnServiceCharges)) {
            $json['return_service_charges'] = $this->returnServiceCharges['value'];
        }
        if (isset($this->returnTaxes)) {
            $json['return_taxes']           = $this->returnTaxes;
        }
        if (isset($this->returnDiscounts)) {
            $json['return_discounts']       = $this->returnDiscounts;
        }
        if (!empty($this->returnTips)) {
            $json['return_tips']            = $this->returnTips['value'];
        }
        if (isset($this->roundingAdjustment)) {
            $json['rounding_adjustment']    = $this->roundingAdjustment;
        }
        if (isset($this->returnAmounts)) {
            $json['return_amounts']         = $this->returnAmounts;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
