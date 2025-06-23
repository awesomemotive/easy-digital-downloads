<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelPaymentByIdempotencyKeyResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model CancelPaymentByIdempotencyKeyResponse
 *
 * @see CancelPaymentByIdempotencyKeyResponse
 */
class CancelPaymentByIdempotencyKeyResponseBuilder
{
    /**
     * @var CancelPaymentByIdempotencyKeyResponse
     */
    private $instance;

    private function __construct(CancelPaymentByIdempotencyKeyResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Payment By Idempotency Key Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CancelPaymentByIdempotencyKeyResponse());
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
     * Initializes a new Cancel Payment By Idempotency Key Response object.
     */
    public function build(): CancelPaymentByIdempotencyKeyResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
