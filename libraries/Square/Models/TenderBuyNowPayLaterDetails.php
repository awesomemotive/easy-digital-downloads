<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents the details of a tender with `type` `BUY_NOW_PAY_LATER`.
 */
class TenderBuyNowPayLaterDetails implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $buyNowPayLaterBrand;

    /**
     * @var string|null
     */
    private $status;

    /**
     * Returns Buy Now Pay Later Brand.
     */
    public function getBuyNowPayLaterBrand(): ?string
    {
        return $this->buyNowPayLaterBrand;
    }

    /**
     * Sets Buy Now Pay Later Brand.
     *
     * @maps buy_now_pay_later_brand
     */
    public function setBuyNowPayLaterBrand(?string $buyNowPayLaterBrand): void
    {
        $this->buyNowPayLaterBrand = $buyNowPayLaterBrand;
    }

    /**
     * Returns Status.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
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
        if (isset($this->buyNowPayLaterBrand)) {
            $json['buy_now_pay_later_brand'] = $this->buyNowPayLaterBrand;
        }
        if (isset($this->status)) {
            $json['status']                  = $this->status;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
