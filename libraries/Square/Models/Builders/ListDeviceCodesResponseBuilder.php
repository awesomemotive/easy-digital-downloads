<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceCode;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListDeviceCodesResponse;

/**
 * Builder for model ListDeviceCodesResponse
 *
 * @see ListDeviceCodesResponse
 */
class ListDeviceCodesResponseBuilder
{
    /**
     * @var ListDeviceCodesResponse
     */
    private $instance;

    private function __construct(ListDeviceCodesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Device Codes Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListDeviceCodesResponse());
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
     * Sets device codes field.
     *
     * @param DeviceCode[]|null $value
     */
    public function deviceCodes(?array $value): self
    {
        $this->instance->setDeviceCodes($value);
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
     * Initializes a new List Device Codes Response object.
     */
    public function build(): ListDeviceCodesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
