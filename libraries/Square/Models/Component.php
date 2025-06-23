<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The wrapper object for the component entries of a given component type.
 */
class Component implements \JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var DeviceComponentDetailsApplicationDetails|null
     */
    private $applicationDetails;

    /**
     * @var DeviceComponentDetailsCardReaderDetails|null
     */
    private $cardReaderDetails;

    /**
     * @var DeviceComponentDetailsBatteryDetails|null
     */
    private $batteryDetails;

    /**
     * @var DeviceComponentDetailsWiFiDetails|null
     */
    private $wifiDetails;

    /**
     * @var DeviceComponentDetailsEthernetDetails|null
     */
    private $ethernetDetails;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Returns Type.
     * An enum for ComponentType.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * An enum for ComponentType.
     *
     * @required
     * @maps type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Application Details.
     */
    public function getApplicationDetails(): ?DeviceComponentDetailsApplicationDetails
    {
        return $this->applicationDetails;
    }

    /**
     * Sets Application Details.
     *
     * @maps application_details
     */
    public function setApplicationDetails(?DeviceComponentDetailsApplicationDetails $applicationDetails): void
    {
        $this->applicationDetails = $applicationDetails;
    }

    /**
     * Returns Card Reader Details.
     */
    public function getCardReaderDetails(): ?DeviceComponentDetailsCardReaderDetails
    {
        return $this->cardReaderDetails;
    }

    /**
     * Sets Card Reader Details.
     *
     * @maps card_reader_details
     */
    public function setCardReaderDetails(?DeviceComponentDetailsCardReaderDetails $cardReaderDetails): void
    {
        $this->cardReaderDetails = $cardReaderDetails;
    }

    /**
     * Returns Battery Details.
     */
    public function getBatteryDetails(): ?DeviceComponentDetailsBatteryDetails
    {
        return $this->batteryDetails;
    }

    /**
     * Sets Battery Details.
     *
     * @maps battery_details
     */
    public function setBatteryDetails(?DeviceComponentDetailsBatteryDetails $batteryDetails): void
    {
        $this->batteryDetails = $batteryDetails;
    }

    /**
     * Returns Wifi Details.
     */
    public function getWifiDetails(): ?DeviceComponentDetailsWiFiDetails
    {
        return $this->wifiDetails;
    }

    /**
     * Sets Wifi Details.
     *
     * @maps wifi_details
     */
    public function setWifiDetails(?DeviceComponentDetailsWiFiDetails $wifiDetails): void
    {
        $this->wifiDetails = $wifiDetails;
    }

    /**
     * Returns Ethernet Details.
     */
    public function getEthernetDetails(): ?DeviceComponentDetailsEthernetDetails
    {
        return $this->ethernetDetails;
    }

    /**
     * Sets Ethernet Details.
     *
     * @maps ethernet_details
     */
    public function setEthernetDetails(?DeviceComponentDetailsEthernetDetails $ethernetDetails): void
    {
        $this->ethernetDetails = $ethernetDetails;
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
        $json['type']                    = $this->type;
        if (isset($this->applicationDetails)) {
            $json['application_details'] = $this->applicationDetails;
        }
        if (isset($this->cardReaderDetails)) {
            $json['card_reader_details'] = $this->cardReaderDetails;
        }
        if (isset($this->batteryDetails)) {
            $json['battery_details']     = $this->batteryDetails;
        }
        if (isset($this->wifiDetails)) {
            $json['wifi_details']        = $this->wifiDetails;
        }
        if (isset($this->ethernetDetails)) {
            $json['ethernet_details']    = $this->ethernetDetails;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
