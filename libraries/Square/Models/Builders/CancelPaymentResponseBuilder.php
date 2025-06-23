<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelPaymentResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Payment;

/**
 * Builder for model CancelPaymentResponse
 *
 * @see CancelPaymentResponse
 */
class CancelPaymentResponseBuilder
{
    /**
     * @var CancelPaymentResponse
     */
    private $instance;

    private function __construct(CancelPaymentResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Payment Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CancelPaymentResponse());
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
     * Sets payment field.
     *
     * @param Payment|null $value
     */
    public function payment(?Payment $value): self
    {
        $this->instance->setPayment($value);
        return $this;
    }

    /**
     * Initializes a new Cancel Payment Response object.
     */
    public function build(): CancelPaymentResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
