<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceComponentDetailsBatteryDetails implements \JsonSerializable
{
    /**
     * @var array
     */
    private $visiblePercent = [];

    /**
     * @var string|null
     */
    private $externalPower;

    /**
     * Returns Visible Percent.
     * The battery charge percentage as displayed on the device.
     */
    public function getVisiblePercent(): ?int
    {
        if (count($this->visiblePercent) == 0) {
            return null;
        }
        return $this->visiblePercent['value'];
    }

    /**
     * Sets Visible Percent.
     * The battery charge percentage as displayed on the device.
     *
     * @maps visible_percent
     */
    public function setVisiblePercent(?int $visiblePercent): void
    {
        $this->visiblePercent['value'] = $visiblePercent;
    }

    /**
     * Unsets Visible Percent.
     * The battery charge percentage as displayed on the device.
     */
    public function unsetVisiblePercent(): void
    {
        $this->visiblePercent = [];
    }

    /**
     * Returns External Power.
     * An enum for ExternalPower.
     */
    public function getExternalPower(): ?string
    {
        return $this->externalPower;
    }

    /**
     * Sets External Power.
     * An enum for ExternalPower.
     *
     * @maps external_power
     */
    public function setExternalPower(?string $externalPower): void
    {
        $this->externalPower = $externalPower;
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
        if (!empty($this->visiblePercent)) {
            $json['visible_percent'] = $this->visiblePercent['value'];
        }
        if (isset($this->externalPower)) {
            $json['external_power']  = $this->externalPower;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
