<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchAvailabilityFilter;
use EDD\Vendor\Square\Models\SegmentFilter;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model SearchAvailabilityFilter
 *
 * @see SearchAvailabilityFilter
 */
class SearchAvailabilityFilterBuilder
{
    /**
     * @var SearchAvailabilityFilter
     */
    private $instance;

    private function __construct(SearchAvailabilityFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Availability Filter Builder object.
     *
     * @param TimeRange $startAtRange
     */
    public static function init(TimeRange $startAtRange): self
    {
        return new self(new SearchAvailabilityFilter($startAtRange));
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets segment filters field.
     *
     * @param SegmentFilter[]|null $value
     */
    public function segmentFilters(?array $value): self
    {
        $this->instance->setSegmentFilters($value);
        return $this;
    }

    /**
     * Unsets segment filters field.
     */
    public function unsetSegmentFilters(): self
    {
        $this->instance->unsetSegmentFilters();
        return $this;
    }

    /**
     * Sets booking id field.
     *
     * @param string|null $value
     */
    public function bookingId(?string $value): self
    {
        $this->instance->setBookingId($value);
        return $this;
    }

    /**
     * Unsets booking id field.
     */
    public function unsetBookingId(): self
    {
        $this->instance->unsetBookingId();
        return $this;
    }

    /**
     * Initializes a new Search Availability Filter object.
     */
    public function build(): SearchAvailabilityFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
