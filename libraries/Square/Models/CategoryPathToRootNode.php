<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * A node in the path from a retrieved category to its root node.
 */
class CategoryPathToRootNode implements \JsonSerializable
{
    /**
     * @var array
     */
    private $categoryId = [];

    /**
     * @var array
     */
    private $categoryName = [];

    /**
     * Returns Category Id.
     * The category's ID.
     */
    public function getCategoryId(): ?string
    {
        if (count($this->categoryId) == 0) {
            return null;
        }
        return $this->categoryId['value'];
    }

    /**
     * Sets Category Id.
     * The category's ID.
     *
     * @maps category_id
     */
    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId['value'] = $categoryId;
    }

    /**
     * Unsets Category Id.
     * The category's ID.
     */
    public function unsetCategoryId(): void
    {
        $this->categoryId = [];
    }

    /**
     * Returns Category Name.
     * The category's name.
     */
    public function getCategoryName(): ?string
    {
        if (count($this->categoryName) == 0) {
            return null;
        }
        return $this->categoryName['value'];
    }

    /**
     * Sets Category Name.
     * The category's name.
     *
     * @maps category_name
     */
    public function setCategoryName(?string $categoryName): void
    {
        $this->categoryName['value'] = $categoryName;
    }

    /**
     * Unsets Category Name.
     * The category's name.
     */
    public function unsetCategoryName(): void
    {
        $this->categoryName = [];
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
        if (!empty($this->categoryId)) {
            $json['category_id']   = $this->categoryId['value'];
        }
        if (!empty($this->categoryName)) {
            $json['category_name'] = $this->categoryName['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
