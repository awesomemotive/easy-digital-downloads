<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Location-specific overrides for specified properties of a `CatalogModifier` object.
 */
class ModifierLocationOverrides implements \JsonSerializable
{
    /**
     * @var array
     */
    private $locationId = [];

    /**
     * @var Money|null
     */
    private $priceMoney;

    /**
     * @var bool|null
     */
    private $soldOut;

    /**
     * Returns Location Id.
     * The ID of the `Location` object representing the location. This can include a deactivated location.
     */
    public function getLocationId(): ?string
    {
        if (count($this->locationId) == 0) {
            return null;
        }
        return $this->locationId['value'];
    }

    /**
     * Sets Location Id.
     * The ID of the `Location` object representing the location. This can include a deactivated location.
     *
     * @maps location_id
     */
    public function setLocationId(?string $locationId): void
    {
        $this->locationId['value'] = $locationId;
    }

    /**
     * Unsets Location Id.
     * The ID of the `Location` object representing the location. This can include a deactivated location.
     */
    public function unsetLocationId(): void
    {
        $this->locationId = [];
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
     * Returns Sold Out.
     * Indicates whether the modifier is sold out at the specified location or not. As an example, for
     * cheese (modifier) burger (item), when the modifier is sold out, it is the cheese, but not the burger,
     * that is sold out.
     * The seller can manually set this sold out status. Attempts by an application to set this attribute
     * are ignored.
     */
    public function getSoldOut(): ?bool
    {
        return $this->soldOut;
    }

    /**
     * Sets Sold Out.
     * Indicates whether the modifier is sold out at the specified location or not. As an example, for
     * cheese (modifier) burger (item), when the modifier is sold out, it is the cheese, but not the burger,
     * that is sold out.
     * The seller can manually set this sold out status. Attempts by an application to set this attribute
     * are ignored.
     *
     * @maps sold_out
     */
    public function setSoldOut(?bool $soldOut): void
    {
        $this->soldOut = $soldOut;
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
        if (!empty($this->locationId)) {
            $json['location_id'] = $this->locationId['value'];
        }
        if (isset($this->priceMoney)) {
            $json['price_money'] = $this->priceMoney;
        }
        if (isset($this->soldOut)) {
            $json['sold_out']    = $this->soldOut;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
