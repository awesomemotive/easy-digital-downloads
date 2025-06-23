<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceComponentDetailsNetworkInterfaceDetails implements \JsonSerializable
{
    /**
     * @var array
     */
    private $ipAddressV4 = [];

    /**
     * Returns Ip Address V4.
     * The string representation of the device’s IPv4 address.
     */
    public function getIpAddressV4(): ?string
    {
        if (count($this->ipAddressV4) == 0) {
            return null;
        }
        return $this->ipAddressV4['value'];
    }

    /**
     * Sets Ip Address V4.
     * The string representation of the device’s IPv4 address.
     *
     * @maps ip_address_v4
     */
    public function setIpAddressV4(?string $ipAddressV4): void
    {
        $this->ipAddressV4['value'] = $ipAddressV4;
    }

    /**
     * Unsets Ip Address V4.
     * The string representation of the device’s IPv4 address.
     */
    public function unsetIpAddressV4(): void
    {
        $this->ipAddressV4 = [];
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
        if (!empty($this->ipAddressV4)) {
            $json['ip_address_v4'] = $this->ipAddressV4['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
