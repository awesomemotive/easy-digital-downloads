<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveTransactionResponse;
use EDD\Vendor\Square\Models\Transaction;

/**
 * Builder for model RetrieveTransactionResponse
 *
 * @see RetrieveTransactionResponse
 */
class RetrieveTransactionResponseBuilder
{
    /**
     * @var RetrieveTransactionResponse
     */
    private $instance;

    private function __construct(RetrieveTransactionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Transaction Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveTransactionResponse());
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
     * Sets transaction field.
     *
     * @param Transaction|null $value
     */
    public function transaction(?Transaction $value): self
    {
        $this->instance->setTransaction($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Transaction Response object.
     */
    public function build(): RetrieveTransactionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
