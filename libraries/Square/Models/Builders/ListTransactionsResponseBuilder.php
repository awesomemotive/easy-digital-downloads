<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListTransactionsResponse;
use EDD\Vendor\Square\Models\Transaction;

/**
 * Builder for model ListTransactionsResponse
 *
 * @see ListTransactionsResponse
 */
class ListTransactionsResponseBuilder
{
    /**
     * @var ListTransactionsResponse
     */
    private $instance;

    private function __construct(ListTransactionsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Transactions Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListTransactionsResponse());
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
     * Sets transactions field.
     *
     * @param Transaction[]|null $value
     */
    public function transactions(?array $value): self
    {
        $this->instance->setTransactions($value);
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
     * Initializes a new List Transactions Response object.
     */
    public function build(): ListTransactionsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
