<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CloneOrderRequest;

/**
 * Builder for model CloneOrderRequest
 *
 * @see CloneOrderRequest
 */
class CloneOrderRequestBuilder
{
    /**
     * @var CloneOrderRequest
     */
    private $instance;

    private function __construct(CloneOrderRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Clone Order Request Builder object.
     *
     * @param string $orderId
     */
    public static function init(string $orderId): self
    {
        return new self(new CloneOrderRequest($orderId));
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
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
     * Initializes a new Clone Order Request object.
     */
    public function build(): CloneOrderRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
