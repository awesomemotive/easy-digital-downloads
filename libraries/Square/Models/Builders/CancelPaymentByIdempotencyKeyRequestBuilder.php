<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelPaymentByIdempotencyKeyRequest;

/**
 * Builder for model CancelPaymentByIdempotencyKeyRequest
 *
 * @see CancelPaymentByIdempotencyKeyRequest
 */
class CancelPaymentByIdempotencyKeyRequestBuilder
{
    /**
     * @var CancelPaymentByIdempotencyKeyRequest
     */
    private $instance;

    private function __construct(CancelPaymentByIdempotencyKeyRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Payment By Idempotency Key Request Builder object.
     *
     * @param string $idempotencyKey
     */
    public static function init(string $idempotencyKey): self
    {
        return new self(new CancelPaymentByIdempotencyKeyRequest($idempotencyKey));
    }

    /**
     * Initializes a new Cancel Payment By Idempotency Key Request object.
     */
    public function build(): CancelPaymentByIdempotencyKeyRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
