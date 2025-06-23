<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\InvoiceSort;

/**
 * Builder for model InvoiceSort
 *
 * @see InvoiceSort
 */
class InvoiceSortBuilder
{
    /**
     * @var InvoiceSort
     */
    private $instance;

    private function __construct(InvoiceSort $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Invoice Sort Builder object.
     */
    public static function init(): self
    {
        return new self(new InvoiceSort());
    }

    /**
     * Sets order field.
     *
     * @param string|null $value
     */
    public function order(?string $value): self
    {
        $this->instance->setOrder($value);
        return $this;
    }

    /**
     * Initializes a new Invoice Sort object.
     */
    public function build(): InvoiceSort
    {
        return CoreHelper::clone($this->instance);
    }
}
