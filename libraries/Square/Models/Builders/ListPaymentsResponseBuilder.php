<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListPaymentsResponse;
use EDD\Vendor\Square\Models\Payment;

/**
 * Builder for model ListPaymentsResponse
 *
 * @see ListPaymentsResponse
 */
class ListPaymentsResponseBuilder
{
    /**
     * @var ListPaymentsResponse
     */
    private $instance;

    private function __construct(ListPaymentsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payments Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPaymentsResponse());
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
     * Sets payments field.
     *
     * @param Payment[]|null $value
     */
    public function payments(?array $value): self
    {
        $this->instance->setPayments($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Initializes a new List Payments Response object.
     */
    public function build(): ListPaymentsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
