<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a file attached to an [invoice]($m/Invoice).
 */
class InvoiceAttachment implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $filename;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int|null
     */
    private $filesize;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var string|null
     */
    private $mimeType;

    /**
     * @var string|null
     */
    private $uploadedAt;

    /**
     * Returns Id.
     * The Square-assigned ID of the attachment.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The Square-assigned ID of the attachment.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Filename.
     * The file name of the attachment, which is displayed on the invoice.
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Sets Filename.
     * The file name of the attachment, which is displayed on the invoice.
     *
     * @maps filename
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Returns Description.
     * The description of the attachment, which is displayed on the invoice.
     * This field maps to the seller-defined **Message** field.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets Description.
     * The description of the attachment, which is displayed on the invoice.
     * This field maps to the seller-defined **Message** field.
     *
     * @maps description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Returns Filesize.
     * The file size of the attachment in bytes.
     */
    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    /**
     * Sets Filesize.
     * The file size of the attachment in bytes.
     *
     * @maps filesize
     */
    public function setFilesize(?int $filesize): void
    {
        $this->filesize = $filesize;
    }

    /**
     * Returns Hash.
     * The MD5 hash that was generated from the file contents.
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * Sets Hash.
     * The MD5 hash that was generated from the file contents.
     *
     * @maps hash
     */
    public function setHash(?string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * Returns Mime Type.
     * The mime type of the attachment.
     * The following mime types are supported:
     * image/gif, image/jpeg, image/png, image/tiff, image/bmp, application/pdf.
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * Sets Mime Type.
     * The mime type of the attachment.
     * The following mime types are supported:
     * image/gif, image/jpeg, image/png, image/tiff, image/bmp, application/pdf.
     *
     * @maps mime_type
     */
    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Returns Uploaded At.
     * The timestamp when the attachment was uploaded, in RFC 3339 format.
     */
    public function getUploadedAt(): ?string
    {
        return $this->uploadedAt;
    }

    /**
     * Sets Uploaded At.
     * The timestamp when the attachment was uploaded, in RFC 3339 format.
     *
     * @maps uploaded_at
     */
    public function setUploadedAt(?string $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
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
            $json['id']          = $this->id;
        }
        if (isset($this->filename)) {
            $json['filename']    = $this->filename;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->filesize)) {
            $json['filesize']    = $this->filesize;
        }
        if (isset($this->hash)) {
            $json['hash']        = $this->hash;
        }
        if (isset($this->mimeType)) {
            $json['mime_type']   = $this->mimeType;
        }
        if (isset($this->uploadedAt)) {
            $json['uploaded_at'] = $this->uploadedAt;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
