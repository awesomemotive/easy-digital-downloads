<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Invoice;
use EDD\Vendor\Square\Models\SearchInvoicesResponse;

/**
 * Builder for model SearchInvoicesResponse
 *
 * @see SearchInvoicesResponse
 */
class SearchInvoicesResponseBuilder
{
    /**
     * @var SearchInvoicesResponse
     */
    private $instance;

    private function __construct(SearchInvoicesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Invoices Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchInvoicesResponse());
    }

    /**
     * Sets invoices field.
     *
     * @param Invoice[]|null $value
     */
    public function invoices(?array $value): self
    {
        $this->instance->setInvoices($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
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
     * Initializes a new Search Invoices Response object.
     */
    public function build(): SearchInvoicesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
