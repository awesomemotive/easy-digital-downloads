<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * A range of purchase price that qualifies.
 */
class CheckoutMerchantSettingsPaymentMethodsAfterpayClearpayEligibilityRange implements \JsonSerializable
{
    /**
     * @var Money
     */
    private $min;

    /**
     * @var Money
     */
    private $max;

    /**
     * @param Money $min
     * @param Money $max
     */
    public function __construct(Money $min, Money $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Returns Min.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getMin(): Money
    {
        return $this->min;
    }

    /**
     * Sets Min.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @required
     * @maps min
     */
    public function setMin(Money $min): void
    {
        $this->min = $min;
    }

    /**
     * Returns Max.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     */
    public function getMax(): Money
    {
        return $this->max;
    }

    /**
     * Sets Max.
     * Represents an amount of money. `Money` fields can be signed or unsigned.
     * Fields that do not explicitly define whether they are signed or unsigned are
     * considered unsigned and can only hold positive amounts. For signed fields, the
     * sign of the value indicates the purpose of the money transfer. See
     * [Working with Monetary Amounts](https://developer.squareup.com/docs/build-basics/working-with-
     * monetary-amounts)
     * for more information.
     *
     * @required
     * @maps max
     */
    public function setMax(Money $max): void
    {
        $this->max = $max;
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
        $json['min'] = $this->min;
        $json['max'] = $this->max;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
