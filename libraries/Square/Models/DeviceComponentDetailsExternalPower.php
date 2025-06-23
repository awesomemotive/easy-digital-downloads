<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * An enum for ExternalPower.
 */
class DeviceComponentDetailsExternalPower
{
    /**
     * Plugged in and charging.
     */
    public const AVAILABLE_CHARGING = 'AVAILABLE_CHARGING';

    /**
     * Fully charged.
     */
    public const AVAILABLE_NOT_IN_USE = 'AVAILABLE_NOT_IN_USE';

    /**
     * On battery power.
     */
    public const UNAVAILABLE = 'UNAVAILABLE';

    /**
     * Not providing enough power for the device.
     */
    public const AVAILABLE_INSUFFICIENT = 'AVAILABLE_INSUFFICIENT';
}
