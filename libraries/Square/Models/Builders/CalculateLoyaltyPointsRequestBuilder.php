<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CalculateLoyaltyPointsRequest;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model CalculateLoyaltyPointsRequest
 *
 * @see CalculateLoyaltyPointsRequest
 */
class CalculateLoyaltyPointsRequestBuilder
{
    /**
     * @var CalculateLoyaltyPointsRequest
     */
    private $instance;

    private function __construct(CalculateLoyaltyPointsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Calculate Loyalty Points Request Builder object.
     */
    public static function init(): self
    {
        return new self(new CalculateLoyaltyPointsRequest());
    }

    /**
     * Sets order id field.
     *
     * @param string|null $value
     */
    public function orderId(?string $value): self
    {
        $this->instance->setOrderId($value);
        return $this;
    }

    /**
     * Unsets order id field.
     */
    public function unsetOrderId(): self
    {
        $this->instance->unsetOrderId();
        return $this;
    }

    /**
     * Sets transaction amount money field.
     *
     * @param Money|null $value
     */
    public function transactionAmountMoney(?Money $value): self
    {
        $this->instance->setTransactionAmountMoney($value);
        return $this;
    }

    /**
     * Sets loyalty account id field.
     *
     * @param string|null $value
     */
    public function loyaltyAccountId(?string $value): self
    {
        $this->instance->setLoyaltyAccountId($value);
        return $this;
    }

    /**
     * Unsets loyalty account id field.
     */
    public function unsetLoyaltyAccountId(): self
    {
        $this->instance->unsetLoyaltyAccountId();
        return $this;
    }

    /**
     * Initializes a new Calculate Loyalty Points Request object.
     */
    public function build(): CalculateLoyaltyPointsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
