<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BusinessBookingProfile;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveBusinessBookingProfileResponse;

/**
 * Builder for model RetrieveBusinessBookingProfileResponse
 *
 * @see RetrieveBusinessBookingProfileResponse
 */
class RetrieveBusinessBookingProfileResponseBuilder
{
    /**
     * @var RetrieveBusinessBookingProfileResponse
     */
    private $instance;

    private function __construct(RetrieveBusinessBookingProfileResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Business Booking Profile Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveBusinessBookingProfileResponse());
    }

    /**
     * Sets business booking profile field.
     *
     * @param BusinessBookingProfile|null $value
     */
    public function businessBookingProfile(?BusinessBookingProfile $value): self
    {
        $this->instance->setBusinessBookingProfile($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Business Booking Profile Response object.
     */
    public function build(): RetrieveBusinessBookingProfileResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
