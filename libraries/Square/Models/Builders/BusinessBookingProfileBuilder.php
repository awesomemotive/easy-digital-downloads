<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BusinessAppointmentSettings;
use EDD\Vendor\Square\Models\BusinessBookingProfile;

/**
 * Builder for model BusinessBookingProfile
 *
 * @see BusinessBookingProfile
 */
class BusinessBookingProfileBuilder
{
    /**
     * @var BusinessBookingProfile
     */
    private $instance;

    private function __construct(BusinessBookingProfile $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Business Booking Profile Builder object.
     */
    public static function init(): self
    {
        return new self(new BusinessBookingProfile());
    }

    /**
     * Sets seller id field.
     *
     * @param string|null $value
     */
    public function sellerId(?string $value): self
    {
        $this->instance->setSellerId($value);
        return $this;
    }

    /**
     * Unsets seller id field.
     */
    public function unsetSellerId(): self
    {
        $this->instance->unsetSellerId();
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets booking enabled field.
     *
     * @param bool|null $value
     */
    public function bookingEnabled(?bool $value): self
    {
        $this->instance->setBookingEnabled($value);
        return $this;
    }

    /**
     * Unsets booking enabled field.
     */
    public function unsetBookingEnabled(): self
    {
        $this->instance->unsetBookingEnabled();
        return $this;
    }

    /**
     * Sets customer timezone choice field.
     *
     * @param string|null $value
     */
    public function customerTimezoneChoice(?string $value): self
    {
        $this->instance->setCustomerTimezoneChoice($value);
        return $this;
    }

    /**
     * Sets booking policy field.
     *
     * @param string|null $value
     */
    public function bookingPolicy(?string $value): self
    {
        $this->instance->setBookingPolicy($value);
        return $this;
    }

    /**
     * Sets allow user cancel field.
     *
     * @param bool|null $value
     */
    public function allowUserCancel(?bool $value): self
    {
        $this->instance->setAllowUserCancel($value);
        return $this;
    }

    /**
     * Unsets allow user cancel field.
     */
    public function unsetAllowUserCancel(): self
    {
        $this->instance->unsetAllowUserCancel();
        return $this;
    }

    /**
     * Sets business appointment settings field.
     *
     * @param BusinessAppointmentSettings|null $value
     */
    public function businessAppointmentSettings(?BusinessAppointmentSettings $value): self
    {
        $this->instance->setBusinessAppointmentSettings($value);
        return $this;
    }

    /**
     * Sets support seller level writes field.
     *
     * @param bool|null $value
     */
    public function supportSellerLevelWrites(?bool $value): self
    {
        $this->instance->setSupportSellerLevelWrites($value);
        return $this;
    }

    /**
     * Unsets support seller level writes field.
     */
    public function unsetSupportSellerLevelWrites(): self
    {
        $this->instance->unsetSupportSellerLevelWrites();
        return $this;
    }

    /**
     * Initializes a new Business Booking Profile object.
     */
    public function build(): BusinessBookingProfile
    {
        return CoreHelper::clone($this->instance);
    }
}
