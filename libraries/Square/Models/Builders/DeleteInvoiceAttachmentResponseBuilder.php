<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteInvoiceAttachmentResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteInvoiceAttachmentResponse
 *
 * @see DeleteInvoiceAttachmentResponse
 */
class DeleteInvoiceAttachmentResponseBuilder
{
    /**
     * @var DeleteInvoiceAttachmentResponse
     */
    private $instance;

    private function __construct(DeleteInvoiceAttachmentResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Invoice Attachment Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteInvoiceAttachmentResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new Delete Invoice Attachment Response object.
     */
    public function build(): DeleteInvoiceAttachmentResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
