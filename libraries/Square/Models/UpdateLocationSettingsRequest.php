<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class UpdateLocationSettingsRequest implements \JsonSerializable
{
    /**
     * @var CheckoutLocationSettings
     */
    private $locationSettings;

    /**
     * @param CheckoutLocationSettings $locationSettings
     */
    public function __construct(CheckoutLocationSettings $locationSettings)
    {
        $this->locationSettings = $locationSettings;
    }

    /**
     * Returns Location Settings.
     */
    public function getLocationSettings(): CheckoutLocationSettings
    {
        return $this->locationSettings;
    }

    /**
     * Sets Location Settings.
     *
     * @required
     * @maps location_settings
     */
    public function setLocationSettings(CheckoutLocationSettings $locationSettings): void
    {
        $this->locationSettings = $locationSettings;
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
        $json['location_settings'] = $this->locationSettings;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
