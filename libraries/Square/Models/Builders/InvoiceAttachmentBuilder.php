<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\InvoiceAttachment;

/**
 * Builder for model InvoiceAttachment
 *
 * @see InvoiceAttachment
 */
class InvoiceAttachmentBuilder
{
    /**
     * @var InvoiceAttachment
     */
    private $instance;

    private function __construct(InvoiceAttachment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Invoice Attachment Builder object.
     */
    public static function init(): self
    {
        return new self(new InvoiceAttachment());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets filename field.
     *
     * @param string|null $value
     */
    public function filename(?string $value): self
    {
        $this->instance->setFilename($value);
        return $this;
    }

    /**
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value): self
    {
        $this->instance->setDescription($value);
        return $this;
    }

    /**
     * Sets filesize field.
     *
     * @param int|null $value
     */
    public function filesize(?int $value): self
    {
        $this->instance->setFilesize($value);
        return $this;
    }

    /**
     * Sets hash field.
     *
     * @param string|null $value
     */
    public function hash(?string $value): self
    {
        $this->instance->setHash($value);
        return $this;
    }

    /**
     * Sets mime type field.
     *
     * @param string|null $value
     */
    public function mimeType(?string $value): self
    {
        $this->instance->setMimeType($value);
        return $this;
    }

    /**
     * Sets uploaded at field.
     *
     * @param string|null $value
     */
    public function uploadedAt(?string $value): self
    {
        $this->instance->setUploadedAt($value);
        return $this;
    }

    /**
     * Initializes a new Invoice Attachment object.
     */
    public function build(): InvoiceAttachment
    {
        return CoreHelper::clone($this->instance);
    }
}
