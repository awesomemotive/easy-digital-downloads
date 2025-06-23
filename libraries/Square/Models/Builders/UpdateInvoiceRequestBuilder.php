<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Invoice;
use EDD\Vendor\Square\Models\UpdateInvoiceRequest;

/**
 * Builder for model UpdateInvoiceRequest
 *
 * @see UpdateInvoiceRequest
 */
class UpdateInvoiceRequestBuilder
{
    /**
     * @var UpdateInvoiceRequest
     */
    private $instance;

    private function __construct(UpdateInvoiceRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Invoice Request Builder object.
     *
     * @param Invoice $invoice
     */
    public static function init(Invoice $invoice): self
    {
        return new self(new UpdateInvoiceRequest($invoice));
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
     * Unsets idempotency key field.
     */
    public function unsetIdempotencyKey(): self
    {
        $this->instance->unsetIdempotencyKey();
        return $this;
    }

    /**
     * Sets fields to clear field.
     *
     * @param string[]|null $value
     */
    public function fieldsToClear(?array $value): self
    {
        $this->instance->setFieldsToClear($value);
        return $this;
    }

    /**
     * Unsets fields to clear field.
     */
    public function unsetFieldsToClear(): self
    {
        $this->instance->unsetFieldsToClear();
        return $this;
    }

    /**
     * Initializes a new Update Invoice Request object.
     */
    public function build(): UpdateInvoiceRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
