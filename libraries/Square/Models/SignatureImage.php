<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

class SignatureImage implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $imageType;

    /**
     * @var string|null
     */
    private $data;

    /**
     * Returns Image Type.
     * The mime/type of the image data.
     * Use `image/png;base64` for png.
     */
    public function getImageType(): ?string
    {
        return $this->imageType;
    }

    /**
     * Sets Image Type.
     * The mime/type of the image data.
     * Use `image/png;base64` for png.
     *
     * @maps image_type
     */
    public function setImageType(?string $imageType): void
    {
        $this->imageType = $imageType;
    }

    /**
     * Returns Data.
     * The base64 representation of the image.
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * Sets Data.
     * The base64 representation of the image.
     *
     * @maps data
     */
    public function setData(?string $data): void
    {
        $this->data = $data;
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
        if (isset($this->imageType)) {
            $json['image_type'] = $this->imageType;
        }
        if (isset($this->data)) {
            $json['data']       = $this->data;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
