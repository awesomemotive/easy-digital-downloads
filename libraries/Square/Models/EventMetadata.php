<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Contains metadata about a particular [Event]($m/Event).
 */
class EventMetadata implements \JsonSerializable
{
    /**
     * @var array
     */
    private $eventId = [];

    /**
     * @var array
     */
    private $apiVersion = [];

    /**
     * Returns Event Id.
     * A unique ID for the event.
     */
    public function getEventId(): ?string
    {
        if (count($this->eventId) == 0) {
            return null;
        }
        return $this->eventId['value'];
    }

    /**
     * Sets Event Id.
     * A unique ID for the event.
     *
     * @maps event_id
     */
    public function setEventId(?string $eventId): void
    {
        $this->eventId['value'] = $eventId;
    }

    /**
     * Unsets Event Id.
     * A unique ID for the event.
     */
    public function unsetEventId(): void
    {
        $this->eventId = [];
    }

    /**
     * Returns Api Version.
     * The API version of the event. This corresponds to the default API version of the developer
     * application at the time when the event was created.
     */
    public function getApiVersion(): ?string
    {
        if (count($this->apiVersion) == 0) {
            return null;
        }
        return $this->apiVersion['value'];
    }

    /**
     * Sets Api Version.
     * The API version of the event. This corresponds to the default API version of the developer
     * application at the time when the event was created.
     *
     * @maps api_version
     */
    public function setApiVersion(?string $apiVersion): void
    {
        $this->apiVersion['value'] = $apiVersion;
    }

    /**
     * Unsets Api Version.
     * The API version of the event. This corresponds to the default API version of the developer
     * application at the time when the event was created.
     */
    public function unsetApiVersion(): void
    {
        $this->apiVersion = [];
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
        if (!empty($this->eventId)) {
            $json['event_id']    = $this->eventId['value'];
        }
        if (!empty($this->apiVersion)) {
            $json['api_version'] = $this->apiVersion['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
