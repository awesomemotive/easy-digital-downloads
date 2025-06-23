<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListPayoutsResponse;
use EDD\Vendor\Square\Models\Payout;

/**
 * Builder for model ListPayoutsResponse
 *
 * @see ListPayoutsResponse
 */
class ListPayoutsResponseBuilder
{
    /**
     * @var ListPayoutsResponse
     */
    private $instance;

    private function __construct(ListPayoutsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payouts Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPayoutsResponse());
    }

    /**
     * Sets payouts field.
     *
     * @param Payout[]|null $value
     */
    public function payouts(?array $value): self
    {
        $this->instance->setPayouts($value);
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
     * Initializes a new List Payouts Response object.
     */
    public function build(): ListPayoutsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
