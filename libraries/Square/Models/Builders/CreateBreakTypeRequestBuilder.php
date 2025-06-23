<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BreakType;
use EDD\Vendor\Square\Models\CreateBreakTypeRequest;

/**
 * Builder for model CreateBreakTypeRequest
 *
 * @see CreateBreakTypeRequest
 */
class CreateBreakTypeRequestBuilder
{
    /**
     * @var CreateBreakTypeRequest
     */
    private $instance;

    private function __construct(CreateBreakTypeRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Break Type Request Builder object.
     *
     * @param BreakType $breakType
     */
    public static function init(BreakType $breakType): self
    {
        return new self(new CreateBreakTypeRequest($breakType));
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
     * Initializes a new Create Break Type Request object.
     */
    public function build(): CreateBreakTypeRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
