<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class UpdateMerchantSettingsRequest implements \JsonSerializable
{
    /**
     * @var CheckoutMerchantSettings
     */
    private $merchantSettings;

    /**
     * @param CheckoutMerchantSettings $merchantSettings
     */
    public function __construct(CheckoutMerchantSettings $merchantSettings)
    {
        $this->merchantSettings = $merchantSettings;
    }

    /**
     * Returns Merchant Settings.
     */
    public function getMerchantSettings(): CheckoutMerchantSettings
    {
        return $this->merchantSettings;
    }

    /**
     * Sets Merchant Settings.
     *
     * @required
     * @maps merchant_settings
     */
    public function setMerchantSettings(CheckoutMerchantSettings $merchantSettings): void
    {
        $this->merchantSettings = $merchantSettings;
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
        $json['merchant_settings'] = $this->merchantSettings;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
