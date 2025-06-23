<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\InvoiceQuery;
use EDD\Vendor\Square\Models\SearchInvoicesRequest;

/**
 * Builder for model SearchInvoicesRequest
 *
 * @see SearchInvoicesRequest
 */
class SearchInvoicesRequestBuilder
{
    /**
     * @var SearchInvoicesRequest
     */
    private $instance;

    private function __construct(SearchInvoicesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Invoices Request Builder object.
     *
     * @param InvoiceQuery $query
     */
    public static function init(InvoiceQuery $query): self
    {
        return new self(new SearchInvoicesRequest($query));
    }

    /**
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
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
     * Initializes a new Search Invoices Request object.
     */
    public function build(): SearchInvoicesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
