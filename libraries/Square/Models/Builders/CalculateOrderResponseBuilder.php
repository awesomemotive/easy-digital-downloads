<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CalculateOrderResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Order;

/**
 * Builder for model CalculateOrderResponse
 *
 * @see CalculateOrderResponse
 */
class CalculateOrderResponseBuilder
{
    /**
     * @var CalculateOrderResponse
     */
    private $instance;

    private function __construct(CalculateOrderResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Calculate Order Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CalculateOrderResponse());
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
     * Initializes a new Calculate Order Response object.
     */
    public function build(): CalculateOrderResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
