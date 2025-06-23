<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class CheckoutLocationSettingsBranding implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $headerType;

    /**
     * @var array
     */
    private $buttonColor = [];

    /**
     * @var string|null
     */
    private $buttonShape;

    /**
     * Returns Header Type.
     */
    public function getHeaderType(): ?string
    {
        return $this->headerType;
    }

    /**
     * Sets Header Type.
     *
     * @maps header_type
     */
    public function setHeaderType(?string $headerType): void
    {
        $this->headerType = $headerType;
    }

    /**
     * Returns Button Color.
     * The HTML-supported hex color for the button on the checkout page (for example, "#FFFFFF").
     */
    public function getButtonColor(): ?string
    {
        if (count($this->buttonColor) == 0) {
            return null;
        }
        return $this->buttonColor['value'];
    }

    /**
     * Sets Button Color.
     * The HTML-supported hex color for the button on the checkout page (for example, "#FFFFFF").
     *
     * @maps button_color
     */
    public function setButtonColor(?string $buttonColor): void
    {
        $this->buttonColor['value'] = $buttonColor;
    }

    /**
     * Unsets Button Color.
     * The HTML-supported hex color for the button on the checkout page (for example, "#FFFFFF").
     */
    public function unsetButtonColor(): void
    {
        $this->buttonColor = [];
    }

    /**
     * Returns Button Shape.
     */
    public function getButtonShape(): ?string
    {
        return $this->buttonShape;
    }

    /**
     * Sets Button Shape.
     *
     * @maps button_shape
     */
    public function setButtonShape(?string $buttonShape): void
    {
        $this->buttonShape = $buttonShape;
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
        if (isset($this->headerType)) {
            $json['header_type']  = $this->headerType;
        }
        if (!empty($this->buttonColor)) {
            $json['button_color'] = $this->buttonColor['value'];
        }
        if (isset($this->buttonShape)) {
            $json['button_shape'] = $this->buttonShape;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
