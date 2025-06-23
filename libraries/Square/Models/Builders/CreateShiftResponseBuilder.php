<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateShiftResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Shift;

/**
 * Builder for model CreateShiftResponse
 *
 * @see CreateShiftResponse
 */
class CreateShiftResponseBuilder
{
    /**
     * @var CreateShiftResponse
     */
    private $instance;

    private function __construct(CreateShiftResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Shift Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateShiftResponse());
    }

    /**
     * Sets shift field.
     *
     * @param Shift|null $value
     */
    public function shift(?Shift $value): self
    {
        $this->instance->setShift($value);
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
     * Initializes a new Create Shift Response object.
     */
    public function build(): CreateShiftResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
