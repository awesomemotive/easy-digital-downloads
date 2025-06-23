<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateRefundRequest;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CreateRefundRequest
 *
 * @see CreateRefundRequest
 */
class CreateRefundRequestBuilder
{
    /**
     * @var CreateRefundRequest
     */
    private $instance;

    private function __construct(CreateRefundRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Refund Request Builder object.
     *
     * @param string $idempotencyKey
     * @param string $tenderId
     * @param Money $amountMoney
     */
    public static function init(string $idempotencyKey, string $tenderId, Money $amountMoney): self
    {
        return new self(new CreateRefundRequest($idempotencyKey, $tenderId, $amountMoney));
    }

    /**
     * Sets reason field.
     *
     * @param string|null $value
     */
    public function reason(?string $value): self
    {
        $this->instance->setReason($value);
        return $this;
    }

    /**
     * Initializes a new Create Refund Request object.
     */
    public function build(): CreateRefundRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
