<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\GetTerminalCheckoutResponse;
use EDD\Vendor\Square\Models\TerminalCheckout;

/**
 * Builder for model GetTerminalCheckoutResponse
 *
 * @see GetTerminalCheckoutResponse
 */
class GetTerminalCheckoutResponseBuilder
{
    /**
     * @var GetTerminalCheckoutResponse
     */
    private $instance;

    private function __construct(GetTerminalCheckoutResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Get Terminal Checkout Response Builder object.
     */
    public static function init(): self
    {
        return new self(new GetTerminalCheckoutResponse());
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
     * Sets checkout field.
     *
     * @param TerminalCheckout|null $value
     */
    public function checkout(?TerminalCheckout $value): self
    {
        $this->instance->setCheckout($value);
        return $this;
    }

    /**
     * Initializes a new Get Terminal Checkout Response object.
     */
    public function build(): GetTerminalCheckoutResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
