<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventAccumulatePoints;

/**
 * Builder for model LoyaltyEventAccumulatePoints
 *
 * @see LoyaltyEventAccumulatePoints
 */
class LoyaltyEventAccumulatePointsBuilder
{
    /**
     * @var LoyaltyEventAccumulatePoints
     */
    private $instance;

    private function __construct(LoyaltyEventAccumulatePoints $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Accumulate Points Builder object.
     */
    public static function init(): self
    {
        return new self(new LoyaltyEventAccumulatePoints());
    }

    /**
     * Sets loyalty program id field.
     *
     * @param string|null $value
     */
    public function loyaltyProgramId(?string $value): self
    {
        $this->instance->setLoyaltyProgramId($value);
        return $this;
    }

    /**
     * Sets points field.
     *
     * @param int|null $value
     */
    public function points(?int $value): self
    {
        $this->instance->setPoints($value);
        return $this;
    }

    /**
     * Unsets points field.
     */
    public function unsetPoints(): self
    {
        $this->instance->unsetPoints();
        return $this;
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
     * Initializes a new Loyalty Event Accumulate Points object.
     */
    public function build(): LoyaltyEventAccumulatePoints
    {
        return CoreHelper::clone($this->instance);
    }
}
