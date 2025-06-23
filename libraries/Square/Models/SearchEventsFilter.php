<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Criteria to filter events by.
 */
class SearchEventsFilter implements \JsonSerializable
{
    /**
     * @var array
     */
    private $eventTypes = [];

    /**
     * @var array
     */
    private $merchantIds = [];

    /**
     * @var array
     */
    private $locationIds = [];

    /**
     * @var TimeRange|null
     */
    private $createdAt;

    /**
     * Returns Event Types.
     * Filter events by event types.
     *
     * @return string[]|null
     */
    public function getEventTypes(): ?array
    {
        if (count($this->eventTypes) == 0) {
            return null;
        }
        return $this->eventTypes['value'];
    }

    /**
     * Sets Event Types.
     * Filter events by event types.
     *
     * @maps event_types
     *
     * @param string[]|null $eventTypes
     */
    public function setEventTypes(?array $eventTypes): void
    {
        $this->eventTypes['value'] = $eventTypes;
    }

    /**
     * Unsets Event Types.
     * Filter events by event types.
     */
    public function unsetEventTypes(): void
    {
        $this->eventTypes = [];
    }

    /**
     * Returns Merchant Ids.
     * Filter events by merchant.
     *
     * @return string[]|null
     */
    public function getMerchantIds(): ?array
    {
        if (count($this->merchantIds) == 0) {
            return null;
        }
        return $this->merchantIds['value'];
    }

    /**
     * Sets Merchant Ids.
     * Filter events by merchant.
     *
     * @maps merchant_ids
     *
     * @param string[]|null $merchantIds
     */
    public function setMerchantIds(?array $merchantIds): void
    {
        $this->merchantIds['value'] = $merchantIds;
    }

    /**
     * Unsets Merchant Ids.
     * Filter events by merchant.
     */
    public function unsetMerchantIds(): void
    {
        $this->merchantIds = [];
    }

    /**
     * Returns Location Ids.
     * Filter events by location.
     *
     * @return string[]|null
     */
    public function getLocationIds(): ?array
    {
        if (count($this->locationIds) == 0) {
            return null;
        }
        return $this->locationIds['value'];
    }

    /**
     * Sets Location Ids.
     * Filter events by location.
     *
     * @maps location_ids
     *
     * @param string[]|null $locationIds
     */
    public function setLocationIds(?array $locationIds): void
    {
        $this->locationIds['value'] = $locationIds;
    }

    /**
     * Unsets Location Ids.
     * Filter events by location.
     */
    public function unsetLocationIds(): void
    {
        $this->locationIds = [];
    }

    /**
     * Returns Created At.
     * Represents a generic time range. The start and end values are
     * represented in RFC 3339 format. Time ranges are customized to be
     * inclusive or exclusive based on the needs of a particular endpoint.
     * Refer to the relevant endpoint-specific documentation to determine
     * how time ranges are handled.
     */
    public function getCreatedAt(): ?TimeRange
    {
        return $this->createdAt;
    }

    /**
     * Sets Created At.
     * Represents a generic time range. The start and end values are
     * represented in RFC 3339 format. Time ranges are customized to be
     * inclusive or exclusive based on the needs of a particular endpoint.
     * Refer to the relevant endpoint-specific documentation to determine
     * how time ranges are handled.
     *
     * @maps created_at
     */
    public function setCreatedAt(?TimeRange $createdAt): void
    {
        $this->createdAt = $createdAt;
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
        if (!empty($this->eventTypes)) {
            $json['event_types']  = $this->eventTypes['value'];
        }
        if (!empty($this->merchantIds)) {
            $json['merchant_ids'] = $this->merchantIds['value'];
        }
        if (!empty($this->locationIds)) {
            $json['location_ids'] = $this->locationIds['value'];
        }
        if (isset($this->createdAt)) {
            $json['created_at']   = $this->createdAt;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
