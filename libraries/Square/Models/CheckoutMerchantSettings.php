<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CheckoutMerchantSettings implements \JsonSerializable
{
    /**
     * @var CheckoutMerchantSettingsPaymentMethods|null
     */
    private $paymentMethods;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * Returns Payment Methods.
     */
    public function getPaymentMethods(): ?CheckoutMerchantSettingsPaymentMethods
    {
        return $this->paymentMethods;
    }

    /**
     * Sets Payment Methods.
     *
     * @maps payment_methods
     */
    public function setPaymentMethods(?CheckoutMerchantSettingsPaymentMethods $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Returns Updated At.
     * The timestamp when the settings were last updated, in RFC 3339 format.
     * Examples for January 25th, 2020 6:25:34pm Pacific Standard Time:
     * UTC: 2020-01-26T02:25:34Z
     * Pacific Standard Time with UTC offset: 2020-01-25T18:25:34-08:00
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The timestamp when the settings were last updated, in RFC 3339 format.
     * Examples for January 25th, 2020 6:25:34pm Pacific Standard Time:
     * UTC: 2020-01-26T02:25:34Z
     * Pacific Standard Time with UTC offset: 2020-01-25T18:25:34-08:00
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
        if (isset($this->paymentMethods)) {
            $json['payment_methods'] = $this->paymentMethods;
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']      = $this->updatedAt;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
