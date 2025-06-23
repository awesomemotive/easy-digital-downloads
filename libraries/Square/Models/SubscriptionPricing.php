<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Describes the pricing for the subscription.
 */
class SubscriptionPricing implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $type;

    /**
     * @var array
     */
    private $discountIds = [];

    /**
     * @var Money|null
     */
    private $priceMoney;

    /**
     * Returns Type.
     * Determines the pricing of a [Subscription]($m/Subscription)
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * Determines the pricing of a [Subscription]($m/Subscription)
     *
     * @maps type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Discount Ids.
     * The ids of the discount catalog objects
     *
     * @return string[]|null
     */
    public function getDiscountIds(): ?array
    {
        if (count($this->discountIds) == 0) {
            return null;
        }
        return $this->discountIds['value'];
    }

    /**
     * Sets Discount Ids.
     * The ids of the discount catalog objects
     *
     * @maps discount_ids
     *
     * @param string[]|null $discountIds
     */
    public function setDiscountIds(?array $discountIds): void
    {
        $this->discountIds['value'] = $discountIds;
    }

    /**
     * Unsets Discount Ids.
     * The ids of the discount catalog objects
     */
    public function unsetDiscountIds(): void
    {
        $this->discountIds = [];
    }

    /**
     * Returns Price Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getPriceMoney(): ?Money
    {
        return $this->priceMoney;
    }

    /**
     * Sets Price Money.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @maps price_money
     */
    public function setPriceMoney(?Money $priceMoney): void
    {
        $this->priceMoney = $priceMoney;
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
        if (isset($this->type)) {
            $json['type']         = $this->type;
        }
        if (!empty($this->discountIds)) {
            $json['discount_ids'] = $this->discountIds['value'];
        }
        if (isset($this->priceMoney)) {
            $json['price_money']  = $this->priceMoney;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
