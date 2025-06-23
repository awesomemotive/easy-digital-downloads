<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateTerminalCheckoutRequest;
use EDD\Vendor\Square\Models\TerminalCheckout;

/**
 * Builder for model CreateTerminalCheckoutRequest
 *
 * @see CreateTerminalCheckoutRequest
 */
class CreateTerminalCheckoutRequestBuilder
{
    /**
     * @var CreateTerminalCheckoutRequest
     */
    private $instance;

    private function __construct(CreateTerminalCheckoutRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Terminal Checkout Request Builder object.
     *
     * @param string $idempotencyKey
     * @param TerminalCheckout $checkout
     */
    public static function init(string $idempotencyKey, TerminalCheckout $checkout): self
    {
        return new self(new CreateTerminalCheckoutRequest($idempotencyKey, $checkout));
    }

    /**
     * Initializes a new Create Terminal Checkout Request object.
     */
    public function build(): CreateTerminalCheckoutRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
