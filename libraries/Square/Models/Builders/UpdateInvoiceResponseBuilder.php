<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Invoice;
use EDD\Vendor\Square\Models\UpdateInvoiceResponse;

/**
 * Builder for model UpdateInvoiceResponse
 *
 * @see UpdateInvoiceResponse
 */
class UpdateInvoiceResponseBuilder
{
    /**
     * @var UpdateInvoiceResponse
     */
    private $instance;

    private function __construct(UpdateInvoiceResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Invoice Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateInvoiceResponse());
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
     * Initializes a new Update Invoice Response object.
     */
    public function build(): UpdateInvoiceResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
