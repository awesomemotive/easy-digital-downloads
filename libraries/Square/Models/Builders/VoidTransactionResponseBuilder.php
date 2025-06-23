<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\VoidTransactionResponse;

/**
 * Builder for model VoidTransactionResponse
 *
 * @see VoidTransactionResponse
 */
class VoidTransactionResponseBuilder
{
    /**
     * @var VoidTransactionResponse
     */
    private $instance;

    private function __construct(VoidTransactionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Void Transaction Response Builder object.
     */
    public static function init(): self
    {
        return new self(new VoidTransactionResponse());
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
     * Initializes a new Void Transaction Response object.
     */
    public function build(): VoidTransactionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
