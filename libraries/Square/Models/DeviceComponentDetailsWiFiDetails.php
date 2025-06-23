<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceComponentDetailsWiFiDetails implements \JsonSerializable
{
    /**
     * @var array
     */
    private $active = [];

    /**
     * @var array
     */
    private $ssid = [];

    /**
     * @var array
     */
    private $ipAddressV4 = [];

    /**
     * @var array
     */
    private $secureConnection = [];

    /**
     * @var DeviceComponentDetailsMeasurement|null
     */
    private $signalStrength;

    /**
     * Returns Active.
     * A boolean to represent whether the WiFI interface is currently active.
     */
    public function getActive(): ?bool
    {
        if (count($this->active) == 0) {
            return null;
        }
        return $this->active['value'];
    }

    /**
     * Sets Active.
     * A boolean to represent whether the WiFI interface is currently active.
     *
     * @maps active
     */
    public function setActive(?bool $active): void
    {
        $this->active['value'] = $active;
    }

    /**
     * Unsets Active.
     * A boolean to represent whether the WiFI interface is currently active.
     */
    public function unsetActive(): void
    {
        $this->active = [];
    }

    /**
     * Returns Ssid.
     * The name of the connected WIFI network.
     */
    public function getSsid(): ?string
    {
        if (count($this->ssid) == 0) {
            return null;
        }
        return $this->ssid['value'];
    }

    /**
     * Sets Ssid.
     * The name of the connected WIFI network.
     *
     * @maps ssid
     */
    public function setSsid(?string $ssid): void
    {
        $this->ssid['value'] = $ssid;
    }

    /**
     * Unsets Ssid.
     * The name of the connected WIFI network.
     */
    public function unsetSsid(): void
    {
        $this->ssid = [];
    }

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
     * Returns Secure Connection.
     * The security protocol for a secure connection (e.g. WPA2). None provided if the connection
     * is unsecured.
     */
    public function getSecureConnection(): ?string
    {
        if (count($this->secureConnection) == 0) {
            return null;
        }
        return $this->secureConnection['value'];
    }

    /**
     * Sets Secure Connection.
     * The security protocol for a secure connection (e.g. WPA2). None provided if the connection
     * is unsecured.
     *
     * @maps secure_connection
     */
    public function setSecureConnection(?string $secureConnection): void
    {
        $this->secureConnection['value'] = $secureConnection;
    }

    /**
     * Unsets Secure Connection.
     * The security protocol for a secure connection (e.g. WPA2). None provided if the connection
     * is unsecured.
     */
    public function unsetSecureConnection(): void
    {
        $this->secureConnection = [];
    }

    /**
     * Returns Signal Strength.
     * A value qualified by unit of measure.
     */
    public function getSignalStrength(): ?DeviceComponentDetailsMeasurement
    {
        return $this->signalStrength;
    }

    /**
     * Sets Signal Strength.
     * A value qualified by unit of measure.
     *
     * @maps signal_strength
     */
    public function setSignalStrength(?DeviceComponentDetailsMeasurement $signalStrength): void
    {
        $this->signalStrength = $signalStrength;
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
        if (!empty($this->active)) {
            $json['active']            = $this->active['value'];
        }
        if (!empty($this->ssid)) {
            $json['ssid']              = $this->ssid['value'];
        }
        if (!empty($this->ipAddressV4)) {
            $json['ip_address_v4']     = $this->ipAddressV4['value'];
        }
        if (!empty($this->secureConnection)) {
            $json['secure_connection'] = $this->secureConnection['value'];
        }
        if (isset($this->signalStrength)) {
            $json['signal_strength']   = $this->signalStrength;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
