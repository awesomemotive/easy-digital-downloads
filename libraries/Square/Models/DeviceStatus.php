<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class DeviceStatus implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $category;

    /**
     * Returns Category.
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * Sets Category.
     *
     * @maps category
     */
    public function setCategory(?string $category): void
    {
        $this->category = $category;
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
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
