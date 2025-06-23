<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CloneOrderResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Order;

/**
 * Builder for model CloneOrderResponse
 *
 * @see CloneOrderResponse
 */
class CloneOrderResponseBuilder
{
    /**
     * @var CloneOrderResponse
     */
    private $instance;

    private function __construct(CloneOrderResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Clone Order Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CloneOrderResponse());
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
     * Initializes a new Clone Order Response object.
     */
    public function build(): CloneOrderResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
