<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateInvoiceResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Invoice;

/**
 * Builder for model CreateInvoiceResponse
 *
 * @see CreateInvoiceResponse
 */
class CreateInvoiceResponseBuilder
{
    /**
     * @var CreateInvoiceResponse
     */
    private $instance;

    private function __construct(CreateInvoiceResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Invoice Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateInvoiceResponse());
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
     * Initializes a new Create Invoice Response object.
     */
    public function build(): CreateInvoiceResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
