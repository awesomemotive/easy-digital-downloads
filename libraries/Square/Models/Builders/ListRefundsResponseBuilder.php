<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListRefundsResponse;
use EDD\Vendor\Square\Models\Refund;

/**
 * Builder for model ListRefundsResponse
 *
 * @see ListRefundsResponse
 */
class ListRefundsResponseBuilder
{
    /**
     * @var ListRefundsResponse
     */
    private $instance;

    private function __construct(ListRefundsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Refunds Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListRefundsResponse());
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
     * Sets refunds field.
     *
     * @param Refund[]|null $value
     */
    public function refunds(?array $value): self
    {
        $this->instance->setRefunds($value);
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
     * Initializes a new List Refunds Response object.
     */
    public function build(): ListRefundsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
