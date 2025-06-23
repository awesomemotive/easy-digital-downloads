<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Order;
use EDD\Vendor\Square\Models\UpdateOrderRequest;

/**
 * Builder for model UpdateOrderRequest
 *
 * @see UpdateOrderRequest
 */
class UpdateOrderRequestBuilder
{
    /**
     * @var UpdateOrderRequest
     */
    private $instance;

    private function __construct(UpdateOrderRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Order Request Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateOrderRequest());
    }

    /**
     * Sets order field.
     *
     * @param Order|null $value
     */
    public function order(?Order $value): self
    {
        $this->instance->setOrder($value);
        return $this;
    }

    /**
     * Sets fields to clear field.
     *
     * @param string[]|null $value
     */
    public function fieldsToClear(?array $value): self
    {
        $this->instance->setFieldsToClear($value);
        return $this;
    }

    /**
     * Unsets fields to clear field.
     */
    public function unsetFieldsToClear(): self
    {
        $this->instance->unsetFieldsToClear();
        return $this;
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
     * Unsets idempotency key field.
     */
    public function unsetIdempotencyKey(): self
    {
        $this->instance->unsetIdempotencyKey();
        return $this;
    }

    /**
     * Initializes a new Update Order Request object.
     */
    public function build(): UpdateOrderRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
