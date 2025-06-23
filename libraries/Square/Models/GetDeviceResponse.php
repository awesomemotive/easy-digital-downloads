<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class GetDeviceResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var Device|null
     */
    private $device;

    /**
     * Returns Errors.
     * Information about errors encountered during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Information about errors encountered during the request.
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
     * Returns Device.
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * Sets Device.
     *
     * @maps device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
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
            $json['errors'] = $this->errors;
        }
        if (isset($this->device)) {
            $json['device'] = $this->device;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
