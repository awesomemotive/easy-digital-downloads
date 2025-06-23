<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents an input into a call to [SearchVendors]($e/Vendors/SearchVendors).
 */
class SearchVendorsRequest implements \JsonSerializable
{
    /**
     * @var SearchVendorsRequestFilter|null
     */
    private $filter;

    /**
     * @var SearchVendorsRequestSort|null
     */
    private $sort;

    /**
     * @var string|null
     */
    private $cursor;

    /**
     * Returns Filter.
     * Defines supported query expressions to search for vendors by.
     */
    public function getFilter(): ?SearchVendorsRequestFilter
    {
        return $this->filter;
    }

    /**
     * Sets Filter.
     * Defines supported query expressions to search for vendors by.
     *
     * @maps filter
     */
    public function setFilter(?SearchVendorsRequestFilter $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Returns Sort.
     * Defines a sorter used to sort results from [SearchVendors]($e/Vendors/SearchVendors).
     */
    public function getSort(): ?SearchVendorsRequestSort
    {
        return $this->sort;
    }

    /**
     * Sets Sort.
     * Defines a sorter used to sort results from [SearchVendors]($e/Vendors/SearchVendors).
     *
     * @maps sort
     */
    public function setSort(?SearchVendorsRequestSort $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * Returns Cursor.
     * A pagination cursor returned by a previous call to this endpoint.
     * Provide this to retrieve the next set of results for the original query.
     *
     * See the [Pagination](https://developer.squareup.com/docs/working-with-apis/pagination) guide for
     * more information.
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Sets Cursor.
     * A pagination cursor returned by a previous call to this endpoint.
     * Provide this to retrieve the next set of results for the original query.
     *
     * See the [Pagination](https://developer.squareup.com/docs/working-with-apis/pagination) guide for
     * more information.
     *
     * @maps cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
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
        if (isset($this->filter)) {
            $json['filter'] = $this->filter;
        }
        if (isset($this->sort)) {
            $json['sort']   = $this->sort;
        }
        if (isset($this->cursor)) {
            $json['cursor'] = $this->cursor;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
