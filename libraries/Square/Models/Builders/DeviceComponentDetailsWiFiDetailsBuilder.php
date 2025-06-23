<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceComponentDetailsMeasurement;
use EDD\Vendor\Square\Models\DeviceComponentDetailsWiFiDetails;

/**
 * Builder for model DeviceComponentDetailsWiFiDetails
 *
 * @see DeviceComponentDetailsWiFiDetails
 */
class DeviceComponentDetailsWiFiDetailsBuilder
{
    /**
     * @var DeviceComponentDetailsWiFiDetails
     */
    private $instance;

    private function __construct(DeviceComponentDetailsWiFiDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Component Details Wi Fi Details Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceComponentDetailsWiFiDetails());
    }

    /**
     * Sets active field.
     *
     * @param bool|null $value
     */
    public function active(?bool $value): self
    {
        $this->instance->setActive($value);
        return $this;
    }

    /**
     * Unsets active field.
     */
    public function unsetActive(): self
    {
        $this->instance->unsetActive();
        return $this;
    }

    /**
     * Sets ssid field.
     *
     * @param string|null $value
     */
    public function ssid(?string $value): self
    {
        $this->instance->setSsid($value);
        return $this;
    }

    /**
     * Unsets ssid field.
     */
    public function unsetSsid(): self
    {
        $this->instance->unsetSsid();
        return $this;
    }

    /**
     * Sets ip address v 4 field.
     *
     * @param string|null $value
     */
    public function ipAddressV4(?string $value): self
    {
        $this->instance->setIpAddressV4($value);
        return $this;
    }

    /**
     * Unsets ip address v 4 field.
     */
    public function unsetIpAddressV4(): self
    {
        $this->instance->unsetIpAddressV4();
        return $this;
    }

    /**
     * Sets secure connection field.
     *
     * @param string|null $value
     */
    public function secureConnection(?string $value): self
    {
        $this->instance->setSecureConnection($value);
        return $this;
    }

    /**
     * Unsets secure connection field.
     */
    public function unsetSecureConnection(): self
    {
        $this->instance->unsetSecureConnection();
        return $this;
    }

    /**
     * Sets signal strength field.
     *
     * @param DeviceComponentDetailsMeasurement|null $value
     */
    public function signalStrength(?DeviceComponentDetailsMeasurement $value): self
    {
        $this->instance->setSignalStrength($value);
        return $this;
    }

    /**
     * Initializes a new Device Component Details Wi Fi Details object.
     */
    public function build(): DeviceComponentDetailsWiFiDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
