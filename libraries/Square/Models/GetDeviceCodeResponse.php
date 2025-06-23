<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class GetDeviceCodeResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var DeviceCode|null
     */
    private $deviceCode;

    /**
     * Returns Errors.
     * Any errors that occurred during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Any errors that occurred during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Returns Device Code.
     */
    public function getDeviceCode(): ?DeviceCode
    {
        return $this->deviceCode;
    }

    /**
     * Sets Device Code.
     *
     * @maps device_code
     */
    public function setDeviceCode(?DeviceCode $deviceCode): void
    {
        $this->deviceCode = $deviceCode;
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
        if (isset($this->errors)) {
            $json['errors']      = $this->errors;
        }
        if (isset($this->deviceCode)) {
            $json['device_code'] = $this->deviceCode;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
