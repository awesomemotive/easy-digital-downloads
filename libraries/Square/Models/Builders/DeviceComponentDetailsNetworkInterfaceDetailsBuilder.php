<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceComponentDetailsNetworkInterfaceDetails;

/**
 * Builder for model DeviceComponentDetailsNetworkInterfaceDetails
 *
 * @see DeviceComponentDetailsNetworkInterfaceDetails
 */
class DeviceComponentDetailsNetworkInterfaceDetailsBuilder
{
    /**
     * @var DeviceComponentDetailsNetworkInterfaceDetails
     */
    private $instance;

    private function __construct(DeviceComponentDetailsNetworkInterfaceDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Component Details Network Interface Details Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceComponentDetailsNetworkInterfaceDetails());
    }

    /**
     * Sets ip address v 4 field.
     *
     * @param string|null $value
     */
    public function ipAddressV4(?string $value): self
    {
        $this->instance->setIpAddressV4($value);
        return $this;
    }

    /**
     * Unsets ip address v 4 field.
     */
    public function unsetIpAddressV4(): self
    {
        $this->instance->unsetIpAddressV4();
        return $this;
    }

    /**
     * Initializes a new Device Component Details Network Interface Details object.
     */
    public function build(): DeviceComponentDetailsNetworkInterfaceDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
