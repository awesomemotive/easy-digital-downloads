<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CashDrawerShift;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveCashDrawerShiftResponse;

/**
 * Builder for model RetrieveCashDrawerShiftResponse
 *
 * @see RetrieveCashDrawerShiftResponse
 */
class RetrieveCashDrawerShiftResponseBuilder
{
    /**
     * @var RetrieveCashDrawerShiftResponse
     */
    private $instance;

    private function __construct(RetrieveCashDrawerShiftResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Cash Drawer Shift Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveCashDrawerShiftResponse());
    }

    /**
     * Sets cash drawer shift field.
     *
     * @param CashDrawerShift|null $value
     */
    public function cashDrawerShift(?CashDrawerShift $value): self
    {
        $this->instance->setCashDrawerShift($value);
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
     * Initializes a new Retrieve Cash Drawer Shift Response object.
     */
    public function build(): RetrieveCashDrawerShiftResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
