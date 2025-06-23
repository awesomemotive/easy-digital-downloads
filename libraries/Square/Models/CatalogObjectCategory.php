<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * A category that can be assigned to an item or a parent category that can be assigned
 * to another category. For example, a clothing category can be assigned to a t-shirt item or
 * be made as the parent category to the pants category.
 */
class CatalogObjectCategory implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var array
     */
    private $ordinal = [];

    /**
     * Returns Id.
     * The ID of the object's category.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The ID of the object's category.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Ordinal.
     * The order of the object within the context of the category.
     */
    public function getOrdinal(): ?int
    {
        if (count($this->ordinal) == 0) {
            return null;
        }
        return $this->ordinal['value'];
    }

    /**
     * Sets Ordinal.
     * The order of the object within the context of the category.
     *
     * @maps ordinal
     */
    public function setOrdinal(?int $ordinal): void
    {
        $this->ordinal['value'] = $ordinal;
    }

    /**
     * Unsets Ordinal.
     * The order of the object within the context of the category.
     */
    public function unsetOrdinal(): void
    {
        $this->ordinal = [];
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
        if (isset($this->id)) {
            $json['id']      = $this->id;
        }
        if (!empty($this->ordinal)) {
            $json['ordinal'] = $this->ordinal['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
