<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Fields to describe the action that displays QR-Codes.
 */
class QrCodeOptions implements \JsonSerializable
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $barcodeContents;

    /**
     * @param string $title
     * @param string $body
     * @param string $barcodeContents
     */
    public function __construct(string $title, string $body, string $barcodeContents)
    {
        $this->title = $title;
        $this->body = $body;
        $this->barcodeContents = $barcodeContents;
    }

    /**
     * Returns Title.
     * The title text to display in the QR code flow on the Terminal.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets Title.
     * The title text to display in the QR code flow on the Terminal.
     *
     * @required
     * @maps title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns Body.
     * The body text to display in the QR code flow on the Terminal.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets Body.
     * The body text to display in the QR code flow on the Terminal.
     *
     * @required
     * @maps body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Returns Barcode Contents.
     * The text representation of the data to show in the QR code
     * as UTF8-encoded data.
     */
    public function getBarcodeContents(): string
    {
        return $this->barcodeContents;
    }

    /**
     * Sets Barcode Contents.
     * The text representation of the data to show in the QR code
     * as UTF8-encoded data.
     *
     * @required
     * @maps barcode_contents
     */
    public function setBarcodeContents(string $barcodeContents): void
    {
        $this->barcodeContents = $barcodeContents;
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
        $json['title']            = $this->title;
        $json['body']             = $this->body;
        $json['barcode_contents'] = $this->barcodeContents;
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
