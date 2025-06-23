<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * A value qualified by unit of measure.
 */
class DeviceComponentDetailsMeasurement implements \JsonSerializable
{
    /**
     * @var array
     */
    private $value = [];

    /**
     * Returns Value.
     */
    public function getValue(): ?int
    {
        if (count($this->value) == 0) {
            return null;
        }
        return $this->value['value'];
    }

    /**
     * Sets Value.
     *
     * @maps value
     */
    public function setValue(?int $value): void
    {
        $this->value['value'] = $value;
    }

    /**
     * Unsets Value.
     */
    public function unsetValue(): void
    {
        $this->value = [];
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
        if (!empty($this->value)) {
            $json['value'] = $this->value['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
