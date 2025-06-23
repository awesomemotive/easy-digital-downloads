<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListDeviceCodesRequest;

/**
 * Builder for model ListDeviceCodesRequest
 *
 * @see ListDeviceCodesRequest
 */
class ListDeviceCodesRequestBuilder
{
    /**
     * @var ListDeviceCodesRequest
     */
    private $instance;

    private function __construct(ListDeviceCodesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Device Codes Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListDeviceCodesRequest());
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
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Sets product type field.
     *
     * @param string|null $value
     */
    public function productType(?string $value): self
    {
        $this->instance->setProductType($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string[]|null $value
     */
    public function status(?array $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Unsets status field.
     */
    public function unsetStatus(): self
    {
        $this->instance->unsetStatus();
        return $this;
    }

    /**
     * Initializes a new List Device Codes Request object.
     */
    public function build(): ListDeviceCodesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
