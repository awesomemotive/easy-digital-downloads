<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PublishInvoiceRequest;

/**
 * Builder for model PublishInvoiceRequest
 *
 * @see PublishInvoiceRequest
 */
class PublishInvoiceRequestBuilder
{
    /**
     * @var PublishInvoiceRequest
     */
    private $instance;

    private function __construct(PublishInvoiceRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Publish Invoice Request Builder object.
     *
     * @param int $version
     */
    public static function init(int $version): self
    {
        return new self(new PublishInvoiceRequest($version));
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
     * Initializes a new Publish Invoice Request object.
     */
    public function build(): PublishInvoiceRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
