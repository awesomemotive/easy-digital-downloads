<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelInvoiceResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Invoice;

/**
 * Builder for model CancelInvoiceResponse
 *
 * @see CancelInvoiceResponse
 */
class CancelInvoiceResponseBuilder
{
    /**
     * @var CancelInvoiceResponse
     */
    private $instance;

    private function __construct(CancelInvoiceResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Invoice Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CancelInvoiceResponse());
    }

    /**
     * Sets invoice field.
     *
     * @param Invoice|null $value
     */
    public function invoice(?Invoice $value): self
    {
        $this->instance->setInvoice($value);
        return $this;
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
     * Initializes a new Cancel Invoice Response object.
     */
    public function build(): CancelInvoiceResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
