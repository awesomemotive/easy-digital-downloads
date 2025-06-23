<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CaptureTransactionResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model CaptureTransactionResponse
 *
 * @see CaptureTransactionResponse
 */
class CaptureTransactionResponseBuilder
{
    /**
     * @var CaptureTransactionResponse
     */
    private $instance;

    private function __construct(CaptureTransactionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Capture Transaction Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CaptureTransactionResponse());
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
     * Initializes a new Capture Transaction Response object.
     */
    public function build(): CaptureTransactionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
