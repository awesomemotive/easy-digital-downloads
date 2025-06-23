<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Contains query criteria for the search.
 */
class SearchEventsQuery implements \JsonSerializable
{
    /**
     * @var SearchEventsFilter|null
     */
    private $filter;

    /**
     * @var SearchEventsSort|null
     */
    private $sort;

    /**
     * Returns Filter.
     * Criteria to filter events by.
     */
    public function getFilter(): ?SearchEventsFilter
    {
        return $this->filter;
    }

    /**
     * Sets Filter.
     * Criteria to filter events by.
     *
     * @maps filter
     */
    public function setFilter(?SearchEventsFilter $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Returns Sort.
     * Criteria to sort events by.
     */
    public function getSort(): ?SearchEventsSort
    {
        return $this->sort;
    }

    /**
     * Sets Sort.
     * Criteria to sort events by.
     *
     * @maps sort
     */
    public function setSort(?SearchEventsSort $sort): void
    {
        $this->sort = $sort;
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
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
