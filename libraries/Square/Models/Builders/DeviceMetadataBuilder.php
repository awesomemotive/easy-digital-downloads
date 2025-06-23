<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceMetadata;

/**
 * Builder for model DeviceMetadata
 *
 * @see DeviceMetadata
 */
class DeviceMetadataBuilder
{
    /**
     * @var DeviceMetadata
     */
    private $instance;

    private function __construct(DeviceMetadata $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Metadata Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceMetadata());
    }

    /**
     * Sets battery percentage field.
     *
     * @param string|null $value
     */
    public function batteryPercentage(?string $value): self
    {
        $this->instance->setBatteryPercentage($value);
        return $this;
    }

    /**
     * Unsets battery percentage field.
     */
    public function unsetBatteryPercentage(): self
    {
        $this->instance->unsetBatteryPercentage();
        return $this;
    }

    /**
     * Sets charging state field.
     *
     * @param string|null $value
     */
    public function chargingState(?string $value): self
    {
        $this->instance->setChargingState($value);
        return $this;
    }

    /**
     * Unsets charging state field.
     */
    public function unsetChargingState(): self
    {
        $this->instance->unsetChargingState();
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets merchant id field.
     *
     * @param string|null $value
     */
    public function merchantId(?string $value): self
    {
        $this->instance->setMerchantId($value);
        return $this;
    }

    /**
     * Unsets merchant id field.
     */
    public function unsetMerchantId(): self
    {
        $this->instance->unsetMerchantId();
        return $this;
    }

    /**
     * Sets network connection type field.
     *
     * @param string|null $value
     */
    public function networkConnectionType(?string $value): self
    {
        $this->instance->setNetworkConnectionType($value);
        return $this;
    }

    /**
     * Unsets network connection type field.
     */
    public function unsetNetworkConnectionType(): self
    {
        $this->instance->unsetNetworkConnectionType();
        return $this;
    }

    /**
     * Sets payment region field.
     *
     * @param string|null $value
     */
    public function paymentRegion(?string $value): self
    {
        $this->instance->setPaymentRegion($value);
        return $this;
    }

    /**
     * Unsets payment region field.
     */
    public function unsetPaymentRegion(): self
    {
        $this->instance->unsetPaymentRegion();
        return $this;
    }

    /**
     * Sets serial number field.
     *
     * @param string|null $value
     */
    public function serialNumber(?string $value): self
    {
        $this->instance->setSerialNumber($value);
        return $this;
    }

    /**
     * Unsets serial number field.
     */
    public function unsetSerialNumber(): self
    {
        $this->instance->unsetSerialNumber();
        return $this;
    }

    /**
     * Sets os version field.
     *
     * @param string|null $value
     */
    public function osVersion(?string $value): self
    {
        $this->instance->setOsVersion($value);
        return $this;
    }

    /**
     * Unsets os version field.
     */
    public function unsetOsVersion(): self
    {
        $this->instance->unsetOsVersion();
        return $this;
    }

    /**
     * Sets app version field.
     *
     * @param string|null $value
     */
    public function appVersion(?string $value): self
    {
        $this->instance->setAppVersion($value);
        return $this;
    }

    /**
     * Unsets app version field.
     */
    public function unsetAppVersion(): self
    {
        $this->instance->unsetAppVersion();
        return $this;
    }

    /**
     * Sets wifi network name field.
     *
     * @param string|null $value
     */
    public function wifiNetworkName(?string $value): self
    {
        $this->instance->setWifiNetworkName($value);
        return $this;
    }

    /**
     * Unsets wifi network name field.
     */
    public function unsetWifiNetworkName(): self
    {
        $this->instance->unsetWifiNetworkName();
        return $this;
    }

    /**
     * Sets wifi network strength field.
     *
     * @param string|null $value
     */
    public function wifiNetworkStrength(?string $value): self
    {
        $this->instance->setWifiNetworkStrength($value);
        return $this;
    }

    /**
     * Unsets wifi network strength field.
     */
    public function unsetWifiNetworkStrength(): self
    {
        $this->instance->unsetWifiNetworkStrength();
        return $this;
    }

    /**
     * Sets ip address field.
     *
     * @param string|null $value
     */
    public function ipAddress(?string $value): self
    {
        $this->instance->setIpAddress($value);
        return $this;
    }

    /**
     * Unsets ip address field.
     */
    public function unsetIpAddress(): self
    {
        $this->instance->unsetIpAddress();
        return $this;
    }

    /**
     * Initializes a new Device Metadata object.
     */
    public function build(): DeviceMetadata
    {
        return CoreHelper::clone($this->instance);
    }
}
