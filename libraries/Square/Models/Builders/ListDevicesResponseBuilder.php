<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Device;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListDevicesResponse;

/**
 * Builder for model ListDevicesResponse
 *
 * @see ListDevicesResponse
 */
class ListDevicesResponseBuilder
{
    /**
     * @var ListDevicesResponse
     */
    private $instance;

    private function __construct(ListDevicesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Devices Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListDevicesResponse());
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
     * Sets devices field.
     *
     * @param Device[]|null $value
     */
    public function devices(?array $value): self
    {
        $this->instance->setDevices($value);
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
     * Initializes a new List Devices Response object.
     */
    public function build(): ListDevicesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
