<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LocationBookingProfile;

/**
 * Builder for model LocationBookingProfile
 *
 * @see LocationBookingProfile
 */
class LocationBookingProfileBuilder
{
    /**
     * @var LocationBookingProfile
     */
    private $instance;

    private function __construct(LocationBookingProfile $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Location Booking Profile Builder object.
     */
    public static function init(): self
    {
        return new self(new LocationBookingProfile());
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
     * Sets booking site url field.
     *
     * @param string|null $value
     */
    public function bookingSiteUrl(?string $value): self
    {
        $this->instance->setBookingSiteUrl($value);
        return $this;
    }

    /**
     * Unsets booking site url field.
     */
    public function unsetBookingSiteUrl(): self
    {
        $this->instance->unsetBookingSiteUrl();
        return $this;
    }

    /**
     * Sets online booking enabled field.
     *
     * @param bool|null $value
     */
    public function onlineBookingEnabled(?bool $value): self
    {
        $this->instance->setOnlineBookingEnabled($value);
        return $this;
    }

    /**
     * Unsets online booking enabled field.
     */
    public function unsetOnlineBookingEnabled(): self
    {
        $this->instance->unsetOnlineBookingEnabled();
        return $this;
    }

    /**
     * Initializes a new Location Booking Profile object.
     */
    public function build(): LocationBookingProfile
    {
        return CoreHelper::clone($this->instance);
    }
}
