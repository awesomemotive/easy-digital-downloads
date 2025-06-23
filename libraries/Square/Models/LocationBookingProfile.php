<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * The booking profile of a seller's location, including the location's ID and whether the location is
 * enabled for online booking.
 */
class LocationBookingProfile implements \JsonSerializable
{
    /**
     * @var array
     */
    private $locationId = [];

    /**
     * @var array
     */
    private $bookingSiteUrl = [];

    /**
     * @var array
     */
    private $onlineBookingEnabled = [];

    /**
     * Returns Location Id.
     * The ID of the [location](entity:Location).
     */
    public function getLocationId(): ?string
    {
        if (count($this->locationId) == 0) {
            return null;
        }
        return $this->locationId['value'];
    }

    /**
     * Sets Location Id.
     * The ID of the [location](entity:Location).
     *
     * @maps location_id
     */
    public function setLocationId(?string $locationId): void
    {
        $this->locationId['value'] = $locationId;
    }

    /**
     * Unsets Location Id.
     * The ID of the [location](entity:Location).
     */
    public function unsetLocationId(): void
    {
        $this->locationId = [];
    }

    /**
     * Returns Booking Site Url.
     * Url for the online booking site for this location.
     */
    public function getBookingSiteUrl(): ?string
    {
        if (count($this->bookingSiteUrl) == 0) {
            return null;
        }
        return $this->bookingSiteUrl['value'];
    }

    /**
     * Sets Booking Site Url.
     * Url for the online booking site for this location.
     *
     * @maps booking_site_url
     */
    public function setBookingSiteUrl(?string $bookingSiteUrl): void
    {
        $this->bookingSiteUrl['value'] = $bookingSiteUrl;
    }

    /**
     * Unsets Booking Site Url.
     * Url for the online booking site for this location.
     */
    public function unsetBookingSiteUrl(): void
    {
        $this->bookingSiteUrl = [];
    }

    /**
     * Returns Online Booking Enabled.
     * Indicates whether the location is enabled for online booking.
     */
    public function getOnlineBookingEnabled(): ?bool
    {
        if (count($this->onlineBookingEnabled) == 0) {
            return null;
        }
        return $this->onlineBookingEnabled['value'];
    }

    /**
     * Sets Online Booking Enabled.
     * Indicates whether the location is enabled for online booking.
     *
     * @maps online_booking_enabled
     */
    public function setOnlineBookingEnabled(?bool $onlineBookingEnabled): void
    {
        $this->onlineBookingEnabled['value'] = $onlineBookingEnabled;
    }

    /**
     * Unsets Online Booking Enabled.
     * Indicates whether the location is enabled for online booking.
     */
    public function unsetOnlineBookingEnabled(): void
    {
        $this->onlineBookingEnabled = [];
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
        if (!empty($this->locationId)) {
            $json['location_id']            = $this->locationId['value'];
        }
        if (!empty($this->bookingSiteUrl)) {
            $json['booking_site_url']       = $this->bookingSiteUrl['value'];
        }
        if (!empty($this->onlineBookingEnabled)) {
            $json['online_booking_enabled'] = $this->onlineBookingEnabled['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
