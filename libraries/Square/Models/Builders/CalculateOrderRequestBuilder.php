<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CalculateOrderRequest;
use EDD\Vendor\Square\Models\Order;
use EDD\Vendor\Square\Models\OrderReward;

/**
 * Builder for model CalculateOrderRequest
 *
 * @see CalculateOrderRequest
 */
class CalculateOrderRequestBuilder
{
    /**
     * @var CalculateOrderRequest
     */
    private $instance;

    private function __construct(CalculateOrderRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Calculate Order Request Builder object.
     *
     * @param Order $order
     */
    public static function init(Order $order): self
    {
        return new self(new CalculateOrderRequest($order));
    }

    /**
     * Sets proposed rewards field.
     *
     * @param OrderReward[]|null $value
     */
    public function proposedRewards(?array $value): self
    {
        $this->instance->setProposedRewards($value);
        return $this;
    }

    /**
     * Unsets proposed rewards field.
     */
    public function unsetProposedRewards(): self
    {
        $this->instance->unsetProposedRewards();
        return $this;
    }

    /**
     * Initializes a new Calculate Order Request object.
     */
    public function build(): CalculateOrderRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
