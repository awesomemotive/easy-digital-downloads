<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Stores details about a cash refund. Contains only non-confidential information.
 */
class DestinationDetailsCashRefundDetails implements \JsonSerializable
{
    /**
     * @var Money
     */
    private $sellerSuppliedMoney;

    /**
     * @var Money|null
     */
    private $changeBackMoney;

    /**
     * @param Money $sellerSuppliedMoney
     */
    public function __construct(Money $sellerSuppliedMoney)
    {
        $this->sellerSuppliedMoney = $sellerSuppliedMoney;
    }

    /**
     * Returns Seller Supplied Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getSellerSuppliedMoney(): Money
    {
        return $this->sellerSuppliedMoney;
    }

    /**
     * Sets Seller Supplied Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @required
     * @maps seller_supplied_money
     */
    public function setSellerSuppliedMoney(Money $sellerSuppliedMoney): void
    {
        $this->sellerSuppliedMoney = $sellerSuppliedMoney;
    }

    /**
     * Returns Change Back Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getChangeBackMoney(): ?Money
    {
        return $this->changeBackMoney;
    }

    /**
     * Sets Change Back Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps change_back_money
     */
    public function setChangeBackMoney(?Money $changeBackMoney): void
    {
        $this->changeBackMoney = $changeBackMoney;
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
        $json['seller_supplied_money'] = $this->sellerSuppliedMoney;
        if (isset($this->changeBackMoney)) {
            $json['change_back_money'] = $this->changeBackMoney;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
