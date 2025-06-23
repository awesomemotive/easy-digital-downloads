<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TerminalCheckoutQueryFilter;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model TerminalCheckoutQueryFilter
 *
 * @see TerminalCheckoutQueryFilter
 */
class TerminalCheckoutQueryFilterBuilder
{
    /**
     * @var TerminalCheckoutQueryFilter
     */
    private $instance;

    private function __construct(TerminalCheckoutQueryFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Checkout Query Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new TerminalCheckoutQueryFilter());
    }

    /**
     * Sets device id field.
     *
     * @param string|null $value
     */
    public function deviceId(?string $value): self
    {
        $this->instance->setDeviceId($value);
        return $this;
    }

    /**
     * Unsets device id field.
     */
    public function unsetDeviceId(): self
    {
        $this->instance->unsetDeviceId();
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param TimeRange|null $value
     */
    public function createdAt(?TimeRange $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Unsets status field.
     */
    public function unsetStatus(): self
    {
        $this->instance->unsetStatus();
        return $this;
    }

    /**
     * Initializes a new Terminal Checkout Query Filter object.
     */
    public function build(): TerminalCheckoutQueryFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
