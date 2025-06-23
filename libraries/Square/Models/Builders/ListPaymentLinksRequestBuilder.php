<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListPaymentLinksRequest;

/**
 * Builder for model ListPaymentLinksRequest
 *
 * @see ListPaymentLinksRequest
 */
class ListPaymentLinksRequestBuilder
{
    /**
     * @var ListPaymentLinksRequest
     */
    private $instance;

    private function __construct(ListPaymentLinksRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payment Links Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPaymentLinksRequest());
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
     * Initializes a new List Payment Links Request object.
     */
    public function build(): ListPaymentLinksRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
