<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Order;
use EDD\Vendor\Square\Models\PayOrderResponse;

/**
 * Builder for model PayOrderResponse
 *
 * @see PayOrderResponse
 */
class PayOrderResponseBuilder
{
    /**
     * @var PayOrderResponse
     */
    private $instance;

    private function __construct(PayOrderResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Pay Order Response Builder object.
     */
    public static function init(): self
    {
        return new self(new PayOrderResponse());
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
     * Initializes a new Pay Order Response object.
     */
    public function build(): PayOrderResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
