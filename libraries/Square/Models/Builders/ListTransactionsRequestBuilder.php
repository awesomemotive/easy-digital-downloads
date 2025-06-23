<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListTransactionsRequest;

/**
 * Builder for model ListTransactionsRequest
 *
 * @see ListTransactionsRequest
 */
class ListTransactionsRequestBuilder
{
    /**
     * @var ListTransactionsRequest
     */
    private $instance;

    private function __construct(ListTransactionsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Transactions Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListTransactionsRequest());
    }

    /**
     * Sets begin time field.
     *
     * @param string|null $value
     */
    public function beginTime(?string $value): self
    {
        $this->instance->setBeginTime($value);
        return $this;
    }

    /**
     * Unsets begin time field.
     */
    public function unsetBeginTime(): self
    {
        $this->instance->unsetBeginTime();
        return $this;
    }

    /**
     * Sets end time field.
     *
     * @param string|null $value
     */
    public function endTime(?string $value): self
    {
        $this->instance->setEndTime($value);
        return $this;
    }

    /**
     * Unsets end time field.
     */
    public function unsetEndTime(): self
    {
        $this->instance->unsetEndTime();
        return $this;
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
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
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Initializes a new List Transactions Request object.
     */
    public function build(): ListTransactionsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
