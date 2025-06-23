<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateRefundResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Refund;

/**
 * Builder for model CreateRefundResponse
 *
 * @see CreateRefundResponse
 */
class CreateRefundResponseBuilder
{
    /**
     * @var CreateRefundResponse
     */
    private $instance;

    private function __construct(CreateRefundResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Refund Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateRefundResponse());
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
     * Sets refund field.
     *
     * @param Refund|null $value
     */
    public function refund(?Refund $value): self
    {
        $this->instance->setRefund($value);
        return $this;
    }

    /**
     * Initializes a new Create Refund Response object.
     */
    public function build(): CreateRefundResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
