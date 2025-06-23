<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * A tip being returned.
 */
class OrderReturnTip implements \JsonSerializable
{
    /**
     * @var array
     */
    private $uid = [];

    /**
     * @var Money|null
     */
    private $appliedMoney;

    /**
     * @var array
     */
    private $sourceTenderUid = [];

    /**
     * @var array
     */
    private $sourceTenderId = [];

    /**
     * Returns Uid.
     * A unique ID that identifies the tip only within this order.
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
     * A unique ID that identifies the tip only within this order.
     *
     * @maps uid
     */
    public function setUid(?string $uid): void
    {
        $this->uid['value'] = $uid;
    }

    /**
     * Unsets Uid.
     * A unique ID that identifies the tip only within this order.
     */
    public function unsetUid(): void
    {
        $this->uid = [];
    }

    /**
     * Returns Applied Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getAppliedMoney(): ?Money
    {
        return $this->appliedMoney;
    }

    /**
     * Sets Applied Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps applied_money
     */
    public function setAppliedMoney(?Money $appliedMoney): void
    {
        $this->appliedMoney = $appliedMoney;
    }

    /**
     * Returns Source Tender Uid.
     * The tender `uid` from the order that contains the original application of this tip.
     */
    public function getSourceTenderUid(): ?string
    {
        if (count($this->sourceTenderUid) == 0) {
            return null;
        }
        return $this->sourceTenderUid['value'];
    }

    /**
     * Sets Source Tender Uid.
     * The tender `uid` from the order that contains the original application of this tip.
     *
     * @maps source_tender_uid
     */
    public function setSourceTenderUid(?string $sourceTenderUid): void
    {
        $this->sourceTenderUid['value'] = $sourceTenderUid;
    }

    /**
     * Unsets Source Tender Uid.
     * The tender `uid` from the order that contains the original application of this tip.
     */
    public function unsetSourceTenderUid(): void
    {
        $this->sourceTenderUid = [];
    }

    /**
     * Returns Source Tender Id.
     * The tender `id` from the order that contains the original application of this tip.
     */
    public function getSourceTenderId(): ?string
    {
        if (count($this->sourceTenderId) == 0) {
            return null;
        }
        return $this->sourceTenderId['value'];
    }

    /**
     * Sets Source Tender Id.
     * The tender `id` from the order that contains the original application of this tip.
     *
     * @maps source_tender_id
     */
    public function setSourceTenderId(?string $sourceTenderId): void
    {
        $this->sourceTenderId['value'] = $sourceTenderId;
    }

    /**
     * Unsets Source Tender Id.
     * The tender `id` from the order that contains the original application of this tip.
     */
    public function unsetSourceTenderId(): void
    {
        $this->sourceTenderId = [];
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
            $json['uid']               = $this->uid['value'];
        }
        if (isset($this->appliedMoney)) {
            $json['applied_money']     = $this->appliedMoney;
        }
        if (!empty($this->sourceTenderUid)) {
            $json['source_tender_uid'] = $this->sourceTenderUid['value'];
        }
        if (!empty($this->sourceTenderId)) {
            $json['source_tender_id']  = $this->sourceTenderId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
