<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateDeviceCodeResponse;
use EDD\Vendor\Square\Models\DeviceCode;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model CreateDeviceCodeResponse
 *
 * @see CreateDeviceCodeResponse
 */
class CreateDeviceCodeResponseBuilder
{
    /**
     * @var CreateDeviceCodeResponse
     */
    private $instance;

    private function __construct(CreateDeviceCodeResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Device Code Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateDeviceCodeResponse());
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
     * Sets device code field.
     *
     * @param DeviceCode|null $value
     */
    public function deviceCode(?DeviceCode $value): self
    {
        $this->instance->setDeviceCode($value);
        return $this;
    }

    /**
     * Initializes a new Create Device Code Response object.
     */
    public function build(): CreateDeviceCodeResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
