<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class Device implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var DeviceAttributes
     */
    private $attributes;

    /**
     * @var array
     */
    private $components = [];

    /**
     * @var DeviceStatus|null
     */
    private $status;

    /**
     * @param DeviceAttributes $attributes
     */
    public function __construct(DeviceAttributes $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns Id.
     * A synthetic identifier for the device. The identifier includes a standardized prefix and
     * is otherwise an opaque id generated from key device fields.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * A synthetic identifier for the device. The identifier includes a standardized prefix and
     * is otherwise an opaque id generated from key device fields.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Attributes.
     */
    public function getAttributes(): DeviceAttributes
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     *
     * @required
     * @maps attributes
     */
    public function setAttributes(DeviceAttributes $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns Components.
     * A list of components applicable to the device.
     *
     * @return Component[]|null
     */
    public function getComponents(): ?array
    {
        if (count($this->components) == 0) {
            return null;
        }
        return $this->components['value'];
    }

    /**
     * Sets Components.
     * A list of components applicable to the device.
     *
     * @maps components
     *
     * @param Component[]|null $components
     */
    public function setComponents(?array $components): void
    {
        $this->components['value'] = $components;
    }

    /**
     * Unsets Components.
     * A list of components applicable to the device.
     */
    public function unsetComponents(): void
    {
        $this->components = [];
    }

    /**
     * Returns Status.
     */
    public function getStatus(): ?DeviceStatus
    {
        return $this->status;
    }

    /**
     * Sets Status.
     *
     * @maps status
     */
    public function setStatus(?DeviceStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (isset($this->id)) {
            $json['id']         = $this->id;
        }
        $json['attributes']     = $this->attributes;
        if (!empty($this->components)) {
            $json['components'] = $this->components['value'];
        }
        if (isset($this->status)) {
            $json['status']     = $this->status;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
