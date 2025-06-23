<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a response from an update request containing the updated `WageSetting` object
 * or error messages.
 */
class UpdateWageSettingResponse implements \JsonSerializable
{
    /**
     * @var WageSetting|null
     */
    private $wageSetting;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Wage Setting.
     * Represents information about the overtime exemption status, job assignments, and compensation
     * for a [team member]($m/TeamMember).
     */
    public function getWageSetting(): ?WageSetting
    {
        return $this->wageSetting;
    }

    /**
     * Sets Wage Setting.
     * Represents information about the overtime exemption status, job assignments, and compensation
     * for a [team member]($m/TeamMember).
     *
     * @maps wage_setting
     */
    public function setWageSetting(?WageSetting $wageSetting): void
    {
        $this->wageSetting = $wageSetting;
    }

    /**
     * Returns Errors.
     * The errors that occurred during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * The errors that occurred during the request.
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
        if (isset($this->wageSetting)) {
            $json['wage_setting'] = $this->wageSetting;
        }
        if (isset($this->errors)) {
            $json['errors']       = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
