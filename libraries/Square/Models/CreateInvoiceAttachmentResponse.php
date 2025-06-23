<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Represents a [CreateInvoiceAttachment]($e/Invoices/CreateInvoiceAttachment) response.
 */
class CreateInvoiceAttachmentResponse implements \JsonSerializable
{
    /**
     * @var InvoiceAttachment|null
     */
    private $attachment;

    /**
     * @var Error[]|null
     */
    private $errors;

    /**
     * Returns Attachment.
     * Represents a file attached to an [invoice]($m/Invoice).
     */
    public function getAttachment(): ?InvoiceAttachment
    {
        return $this->attachment;
    }

    /**
     * Sets Attachment.
     * Represents a file attached to an [invoice]($m/Invoice).
     *
     * @maps attachment
     */
    public function setAttachment(?InvoiceAttachment $attachment): void
    {
        $this->attachment = $attachment;
    }

    /**
     * Returns Errors.
     * Information about errors encountered during the request.
     *
     * @return Error[]|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Sets Errors.
     * Information about errors encountered during the request.
     *
     * @maps errors
     *
     * @param Error[]|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = $errors;
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
        if (isset($this->attachment)) {
            $json['attachment'] = $this->attachment;
        }
        if (isset($this->errors)) {
            $json['errors']     = $this->errors;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
