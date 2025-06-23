<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\LocationBookingProfile;
use EDD\Vendor\Square\Models\RetrieveLocationBookingProfileResponse;

/**
 * Builder for model RetrieveLocationBookingProfileResponse
 *
 * @see RetrieveLocationBookingProfileResponse
 */
class RetrieveLocationBookingProfileResponseBuilder
{
    /**
     * @var RetrieveLocationBookingProfileResponse
     */
    private $instance;

    private function __construct(RetrieveLocationBookingProfileResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Location Booking Profile Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveLocationBookingProfileResponse());
    }

    /**
     * Sets location booking profile field.
     *
     * @param LocationBookingProfile|null $value
     */
    public function locationBookingProfile(?LocationBookingProfile $value): self
    {
        $this->instance->setLocationBookingProfile($value);
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
     * Initializes a new Retrieve Location Booking Profile Response object.
     */
    public function build(): RetrieveLocationBookingProfileResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
