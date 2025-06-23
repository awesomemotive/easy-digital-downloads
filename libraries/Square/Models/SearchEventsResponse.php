<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Defines the fields that are included in the response body of
 * a request to the [SearchEvents]($e/Events/SearchEvents) endpoint.
 *
 * Note: if there are errors processing the request, the events field will not be
 * present.
 */
class SearchEventsResponse implements \JsonSerializable
{
    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * @var Event[]|null
     */
    private $events;

    /**
     * @var EventMetadata[]|null
     */
    private $metadata;

    /**
     * @var string|null
     */
    private $cursor;

    /**
     * Returns Errors.
     * Information on errors encountered during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Information on errors encountered during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Returns Events.
     * The list of [Event](entity:Event)s returned by the search.
     *
     * @return Event[]|null
     */
    public function getEvents(): ?array
    {
        return $this->events;
    }

    /**
     * Sets Events.
     * The list of [Event](entity:Event)s returned by the search.
     *
     * @maps events
     *
     * @param Event[]|null $events
     */
    public function setEvents(?array $events): void
    {
        $this->events = $events;
    }

    /**
     * Returns Metadata.
     * Contains the metadata of an event. For more information, see [Event](entity:Event).
     *
     * @return EventMetadata[]|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * Sets Metadata.
     * Contains the metadata of an event. For more information, see [Event](entity:Event).
     *
     * @maps metadata
     *
     * @param EventMetadata[]|null $metadata
     */
    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Returns Cursor.
     * When a response is truncated, it includes a cursor that you can use in a subsequent request to fetch
     * the next set of events. If empty, this is the final response.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Sets Cursor.
     * When a response is truncated, it includes a cursor that you can use in a subsequent request to fetch
     * the next set of events. If empty, this is the final response.
     *
     * For more information, see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     * patterns/pagination).
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
        if (isset($this->errors)) {
            $json['errors']   = $this->errors;
        }
        if (isset($this->events)) {
            $json['events']   = $this->events;
        }
        if (isset($this->metadata)) {
            $json['metadata'] = $this->metadata;
        }
        if (isset($this->cursor)) {
            $json['cursor']   = $this->cursor;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
