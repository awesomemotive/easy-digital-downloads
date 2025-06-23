<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PayOrderRequest;

/**
 * Builder for model PayOrderRequest
 *
 * @see PayOrderRequest
 */
class PayOrderRequestBuilder
{
    /**
     * @var PayOrderRequest
     */
    private $instance;

    private function __construct(PayOrderRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Pay Order Request Builder object.
     *
     * @param string $idempotencyKey
     */
    public static function init(string $idempotencyKey): self
    {
        return new self(new PayOrderRequest($idempotencyKey));
    }

    /**
     * Sets order version field.
     *
     * @param int|null $value
     */
    public function orderVersion(?int $value): self
    {
        $this->instance->setOrderVersion($value);
        return $this;
    }

    /**
     * Unsets order version field.
     */
    public function unsetOrderVersion(): self
    {
        $this->instance->unsetOrderVersion();
        return $this;
    }

    /**
     * Sets payment ids field.
     *
     * @param string[]|null $value
     */
    public function paymentIds(?array $value): self
    {
        $this->instance->setPaymentIds($value);
        return $this;
    }

    /**
     * Unsets payment ids field.
     */
    public function unsetPaymentIds(): self
    {
        $this->instance->unsetPaymentIds();
        return $this;
    }

    /**
     * Initializes a new Pay Order Request object.
     */
    public function build(): PayOrderRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
