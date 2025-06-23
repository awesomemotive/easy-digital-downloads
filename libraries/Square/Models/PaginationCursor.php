<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Used *internally* to encapsulate pagination details. The resulting proto will be base62 encoded
 * in order to produce a cursor that can be used externally.
 */
class PaginationCursor implements \JsonSerializable
{
    /**
     * @var array
     */
    private $orderValue = [];

    /**
     * Returns Order Value.
     * The ID of the last resource in the current page. The page can be in an ascending or
     * descending order
     */
    public function getOrderValue(): ?string
    {
        if (count($this->orderValue) == 0) {
            return null;
        }
        return $this->orderValue['value'];
    }

    /**
     * Sets Order Value.
     * The ID of the last resource in the current page. The page can be in an ascending or
     * descending order
     *
     * @maps order_value
     */
    public function setOrderValue(?string $orderValue): void
    {
        $this->orderValue['value'] = $orderValue;
    }

    /**
     * Unsets Order Value.
     * The ID of the last resource in the current page. The page can be in an ascending or
     * descending order
     */
    public function unsetOrderValue(): void
    {
        $this->orderValue = [];
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
        if (!empty($this->orderValue)) {
            $json['order_value'] = $this->orderValue['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
