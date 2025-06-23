<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceAttributes implements \JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $manufacturer;

    /**
     * @var array
     */
    private $model = [];

    /**
     * @var array
     */
    private $name = [];

    /**
     * @var array
     */
    private $manufacturersId = [];

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var array
     */
    private $merchantToken = [];

    /**
     * @param string $manufacturer
     */
    public function __construct(string $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * Returns Type.
     * An enum identifier of the device type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * An enum identifier of the device type.
     *
     * @maps type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Manufacturer.
     * The maker of the device.
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * Sets Manufacturer.
     * The maker of the device.
     *
     * @required
     * @maps manufacturer
     */
    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * Returns Model.
     * The specific model of the device.
     */
    public function getModel(): ?string
    {
        if (count($this->model) == 0) {
            return null;
        }
        return $this->model['value'];
    }

    /**
     * Sets Model.
     * The specific model of the device.
     *
     * @maps model
     */
    public function setModel(?string $model): void
    {
        $this->model['value'] = $model;
    }

    /**
     * Unsets Model.
     * The specific model of the device.
     */
    public function unsetModel(): void
    {
        $this->model = [];
    }

    /**
     * Returns Name.
     * A seller-specified name for the device.
     */
    public function getName(): ?string
    {
        if (count($this->name) == 0) {
            return null;
        }
        return $this->name['value'];
    }

    /**
     * Sets Name.
     * A seller-specified name for the device.
     *
     * @maps name
     */
    public function setName(?string $name): void
    {
        $this->name['value'] = $name;
    }

    /**
     * Unsets Name.
     * A seller-specified name for the device.
     */
    public function unsetName(): void
    {
        $this->name = [];
    }

    /**
     * Returns Manufacturers Id.
     * The manufacturer-supplied identifier for the device (where available). In many cases,
     * this identifier will be a serial number.
     */
    public function getManufacturersId(): ?string
    {
        if (count($this->manufacturersId) == 0) {
            return null;
        }
        return $this->manufacturersId['value'];
    }

    /**
     * Sets Manufacturers Id.
     * The manufacturer-supplied identifier for the device (where available). In many cases,
     * this identifier will be a serial number.
     *
     * @maps manufacturers_id
     */
    public function setManufacturersId(?string $manufacturersId): void
    {
        $this->manufacturersId['value'] = $manufacturersId;
    }

    /**
     * Unsets Manufacturers Id.
     * The manufacturer-supplied identifier for the device (where available). In many cases,
     * this identifier will be a serial number.
     */
    public function unsetManufacturersId(): void
    {
        $this->manufacturersId = [];
    }

    /**
     * Returns Updated At.
     * The RFC 3339-formatted value of the most recent update to the device information.
     * (Could represent any field update on the device.)
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Sets Updated At.
     * The RFC 3339-formatted value of the most recent update to the device information.
     * (Could represent any field update on the device.)
     *
     * @maps updated_at
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Returns Version.
     * The current version of software installed on the device.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Sets Version.
     * The current version of software installed on the device.
     *
     * @maps version
     */
    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    /**
     * Returns Merchant Token.
     * The merchant_token identifying the merchant controlling the device.
     */
    public function getMerchantToken(): ?string
    {
        if (count($this->merchantToken) == 0) {
            return null;
        }
        return $this->merchantToken['value'];
    }

    /**
     * Sets Merchant Token.
     * The merchant_token identifying the merchant controlling the device.
     *
     * @maps merchant_token
     */
    public function setMerchantToken(?string $merchantToken): void
    {
        $this->merchantToken['value'] = $merchantToken;
    }

    /**
     * Unsets Merchant Token.
     * The merchant_token identifying the merchant controlling the device.
     */
    public function unsetMerchantToken(): void
    {
        $this->merchantToken = [];
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
        $json['type']                 = $this->type;
        $json['manufacturer']         = $this->manufacturer;
        if (!empty($this->model)) {
            $json['model']            = $this->model['value'];
        }
        if (!empty($this->name)) {
            $json['name']             = $this->name['value'];
        }
        if (!empty($this->manufacturersId)) {
            $json['manufacturers_id'] = $this->manufacturersId['value'];
        }
        if (isset($this->updatedAt)) {
            $json['updated_at']       = $this->updatedAt;
        }
        if (isset($this->version)) {
            $json['version']          = $this->version;
        }
        if (!empty($this->merchantToken)) {
            $json['merchant_token']   = $this->merchantToken['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
