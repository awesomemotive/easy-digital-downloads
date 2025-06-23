<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceComponentDetailsApplicationDetails implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $applicationType;

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var array
     */
    private $sessionLocation = [];

    /**
     * @var array
     */
    private $deviceCodeId = [];

    /**
     * Returns Application Type.
     */
    public function getApplicationType(): ?string
    {
        return $this->applicationType;
    }

    /**
     * Sets Application Type.
     *
     * @maps application_type
     */
    public function setApplicationType(?string $applicationType): void
    {
        $this->applicationType = $applicationType;
    }

    /**
     * Returns Version.
     * The version of the application.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Sets Version.
     * The version of the application.
     *
     * @maps version
     */
    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    /**
     * Returns Session Location.
     * The location_id of the session for the application.
     */
    public function getSessionLocation(): ?string
    {
        if (count($this->sessionLocation) == 0) {
            return null;
        }
        return $this->sessionLocation['value'];
    }

    /**
     * Sets Session Location.
     * The location_id of the session for the application.
     *
     * @maps session_location
     */
    public function setSessionLocation(?string $sessionLocation): void
    {
        $this->sessionLocation['value'] = $sessionLocation;
    }

    /**
     * Unsets Session Location.
     * The location_id of the session for the application.
     */
    public function unsetSessionLocation(): void
    {
        $this->sessionLocation = [];
    }

    /**
     * Returns Device Code Id.
     * The id of the device code that was used to log in to the device.
     */
    public function getDeviceCodeId(): ?string
    {
        if (count($this->deviceCodeId) == 0) {
            return null;
        }
        return $this->deviceCodeId['value'];
    }

    /**
     * Sets Device Code Id.
     * The id of the device code that was used to log in to the device.
     *
     * @maps device_code_id
     */
    public function setDeviceCodeId(?string $deviceCodeId): void
    {
        $this->deviceCodeId['value'] = $deviceCodeId;
    }

    /**
     * Unsets Device Code Id.
     * The id of the device code that was used to log in to the device.
     */
    public function unsetDeviceCodeId(): void
    {
        $this->deviceCodeId = [];
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
        if (isset($this->applicationType)) {
            $json['application_type'] = $this->applicationType;
        }
        if (isset($this->version)) {
            $json['version']          = $this->version;
        }
        if (!empty($this->sessionLocation)) {
            $json['session_location'] = $this->sessionLocation['value'];
        }
        if (!empty($this->deviceCodeId)) {
            $json['device_code_id']   = $this->deviceCodeId['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
