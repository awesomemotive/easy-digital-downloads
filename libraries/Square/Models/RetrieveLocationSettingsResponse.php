<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class RetrieveLocationSettingsResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var CheckoutLocationSettings|null
     */
    private $locationSettings;

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
     * Returns Location Settings.
     */
    public function getLocationSettings(): ?CheckoutLocationSettings
    {
        return $this->locationSettings;
    }

    /**
     * Sets Location Settings.
     *
     * @maps location_settings
     */
    public function setLocationSettings(?CheckoutLocationSettings $locationSettings): void
    {
        $this->locationSettings = $locationSettings;
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
            $json['errors']            = $this->errors;
        }
        if (isset($this->locationSettings)) {
            $json['location_settings'] = $this->locationSettings;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
