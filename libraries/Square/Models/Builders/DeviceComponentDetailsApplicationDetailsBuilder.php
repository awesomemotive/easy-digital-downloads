<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceComponentDetailsApplicationDetails;

/**
 * Builder for model DeviceComponentDetailsApplicationDetails
 *
 * @see DeviceComponentDetailsApplicationDetails
 */
class DeviceComponentDetailsApplicationDetailsBuilder
{
    /**
     * @var DeviceComponentDetailsApplicationDetails
     */
    private $instance;

    private function __construct(DeviceComponentDetailsApplicationDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Component Details Application Details Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceComponentDetailsApplicationDetails());
    }

    /**
     * Sets application type field.
     *
     * @param string|null $value
     */
    public function applicationType(?string $value): self
    {
        $this->instance->setApplicationType($value);
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param string|null $value
     */
    public function version(?string $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets session location field.
     *
     * @param string|null $value
     */
    public function sessionLocation(?string $value): self
    {
        $this->instance->setSessionLocation($value);
        return $this;
    }

    /**
     * Unsets session location field.
     */
    public function unsetSessionLocation(): self
    {
        $this->instance->unsetSessionLocation();
        return $this;
    }

    /**
     * Sets device code id field.
     *
     * @param string|null $value
     */
    public function deviceCodeId(?string $value): self
    {
        $this->instance->setDeviceCodeId($value);
        return $this;
    }

    /**
     * Unsets device code id field.
     */
    public function unsetDeviceCodeId(): self
    {
        $this->instance->unsetDeviceCodeId();
        return $this;
    }

    /**
     * Initializes a new Device Component Details Application Details object.
     */
    public function build(): DeviceComponentDetailsApplicationDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
