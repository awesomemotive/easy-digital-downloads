<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListCashDrawerShiftEventsRequest;

/**
 * Builder for model ListCashDrawerShiftEventsRequest
 *
 * @see ListCashDrawerShiftEventsRequest
 */
class ListCashDrawerShiftEventsRequestBuilder
{
    /**
     * @var ListCashDrawerShiftEventsRequest
     */
    private $instance;

    private function __construct(ListCashDrawerShiftEventsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Cash Drawer Shift Events Request Builder object.
     *
     * @param string $locationId
     */
    public static function init(string $locationId): self
    {
        return new self(new ListCashDrawerShiftEventsRequest($locationId));
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
     * Initializes a new List Cash Drawer Shift Events Request object.
     */
    public function build(): ListCashDrawerShiftEventsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
