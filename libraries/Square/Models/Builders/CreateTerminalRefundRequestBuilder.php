<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateTerminalRefundRequest;
use EDD\Vendor\Square\Models\TerminalRefund;

/**
 * Builder for model CreateTerminalRefundRequest
 *
 * @see CreateTerminalRefundRequest
 */
class CreateTerminalRefundRequestBuilder
{
    /**
     * @var CreateTerminalRefundRequest
     */
    private $instance;

    private function __construct(CreateTerminalRefundRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Terminal Refund Request Builder object.
     *
     * @param string $idempotencyKey
     */
    public static function init(string $idempotencyKey): self
    {
        return new self(new CreateTerminalRefundRequest($idempotencyKey));
    }

    /**
     * Sets refund field.
     *
     * @param TerminalRefund|null $value
     */
    public function refund(?TerminalRefund $value): self
    {
        $this->instance->setRefund($value);
        return $this;
    }

    /**
     * Initializes a new Create Terminal Refund Request object.
     */
    public function build(): CreateTerminalRefundRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
