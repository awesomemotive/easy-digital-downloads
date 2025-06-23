<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceComponentDetailsCardReaderDetails implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $version;

    /**
     * Returns Version.
     * The version of the card reader.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Sets Version.
     * The version of the card reader.
     *
     * @maps version
     */
    public function setVersion(?string $version): void
    {
        $this->version = $version;
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
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
