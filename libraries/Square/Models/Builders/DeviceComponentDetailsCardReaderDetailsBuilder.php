<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeviceComponentDetailsCardReaderDetails;

/**
 * Builder for model DeviceComponentDetailsCardReaderDetails
 *
 * @see DeviceComponentDetailsCardReaderDetails
 */
class DeviceComponentDetailsCardReaderDetailsBuilder
{
    /**
     * @var DeviceComponentDetailsCardReaderDetails
     */
    private $instance;

    private function __construct(DeviceComponentDetailsCardReaderDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Device Component Details Card Reader Details Builder object.
     */
    public static function init(): self
    {
        return new self(new DeviceComponentDetailsCardReaderDetails());
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
     * Initializes a new Device Component Details Card Reader Details object.
     */
    public function build(): DeviceComponentDetailsCardReaderDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
