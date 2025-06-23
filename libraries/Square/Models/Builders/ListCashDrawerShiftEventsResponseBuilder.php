<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CashDrawerShiftEvent;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListCashDrawerShiftEventsResponse;

/**
 * Builder for model ListCashDrawerShiftEventsResponse
 *
 * @see ListCashDrawerShiftEventsResponse
 */
class ListCashDrawerShiftEventsResponseBuilder
{
    /**
     * @var ListCashDrawerShiftEventsResponse
     */
    private $instance;

    private function __construct(ListCashDrawerShiftEventsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Cash Drawer Shift Events Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCashDrawerShiftEventsResponse());
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
     * Sets cash drawer shift events field.
     *
     * @param CashDrawerShiftEvent[]|null $value
     */
    public function cashDrawerShiftEvents(?array $value): self
    {
        $this->instance->setCashDrawerShiftEvents($value);
        return $this;
    }

    /**
     * Initializes a new List Cash Drawer Shift Events Response object.
     */
    public function build(): ListCashDrawerShiftEventsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
