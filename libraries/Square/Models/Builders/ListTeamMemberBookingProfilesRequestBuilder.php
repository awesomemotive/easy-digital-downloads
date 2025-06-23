<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListTeamMemberBookingProfilesRequest;

/**
 * Builder for model ListTeamMemberBookingProfilesRequest
 *
 * @see ListTeamMemberBookingProfilesRequest
 */
class ListTeamMemberBookingProfilesRequestBuilder
{
    /**
     * @var ListTeamMemberBookingProfilesRequest
     */
    private $instance;

    private function __construct(ListTeamMemberBookingProfilesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Team Member Booking Profiles Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListTeamMemberBookingProfilesRequest());
    }

    /**
     * Sets bookable only field.
     *
     * @param bool|null $value
     */
    public function bookableOnly(?bool $value): self
    {
        $this->instance->setBookableOnly($value);
        return $this;
    }

    /**
     * Unsets bookable only field.
     */
    public function unsetBookableOnly(): self
    {
        $this->instance->unsetBookableOnly();
        return $this;
    }

    /**
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
    }

    /**
     * Unsets limit field.
     */
    public function unsetLimit(): self
    {
        $this->instance->unsetLimit();
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
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
     * Initializes a new List Team Member Booking Profiles Request object.
     */
    public function build(): ListTeamMemberBookingProfilesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
