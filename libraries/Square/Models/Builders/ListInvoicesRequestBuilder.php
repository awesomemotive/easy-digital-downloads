<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListInvoicesRequest;

/**
 * Builder for model ListInvoicesRequest
 *
 * @see ListInvoicesRequest
 */
class ListInvoicesRequestBuilder
{
    /**
     * @var ListInvoicesRequest
     */
    private $instance;

    private function __construct(ListInvoicesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Invoices Request Builder object.
     *
     * @param string $locationId
     */
    public static function init(string $locationId): self
    {
        return new self(new ListInvoicesRequest($locationId));
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
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
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
     * Unsets limit field.
     */
    public function unsetLimit(): self
    {
        $this->instance->unsetLimit();
        return $this;
    }

    /**
     * Initializes a new List Invoices Request object.
     */
    public function build(): ListInvoicesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
