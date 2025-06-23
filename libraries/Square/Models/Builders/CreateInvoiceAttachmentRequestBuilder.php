<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateInvoiceAttachmentRequest;

/**
 * Builder for model CreateInvoiceAttachmentRequest
 *
 * @see CreateInvoiceAttachmentRequest
 */
class CreateInvoiceAttachmentRequestBuilder
{
    /**
     * @var CreateInvoiceAttachmentRequest
     */
    private $instance;

    private function __construct(CreateInvoiceAttachmentRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Invoice Attachment Request Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateInvoiceAttachmentRequest());
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
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
     * Initializes a new Create Invoice Attachment Request object.
     */
    public function build(): CreateInvoiceAttachmentRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
