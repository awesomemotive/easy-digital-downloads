<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateShiftRequest;
use EDD\Vendor\Square\Models\Shift;

/**
 * Builder for model CreateShiftRequest
 *
 * @see CreateShiftRequest
 */
class CreateShiftRequestBuilder
{
    /**
     * @var CreateShiftRequest
     */
    private $instance;

    private function __construct(CreateShiftRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Shift Request Builder object.
     *
     * @param Shift $shift
     */
    public static function init(Shift $shift): self
    {
        return new self(new CreateShiftRequest($shift));
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }

    /**
     * Initializes a new Create Shift Request object.
     */
    public function build(): CreateShiftRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
