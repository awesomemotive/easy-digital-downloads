<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchTerminalRefundsResponse;
use EDD\Vendor\Square\Models\TerminalRefund;

/**
 * Builder for model SearchTerminalRefundsResponse
 *
 * @see SearchTerminalRefundsResponse
 */
class SearchTerminalRefundsResponseBuilder
{
    /**
     * @var SearchTerminalRefundsResponse
     */
    private $instance;

    private function __construct(SearchTerminalRefundsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Terminal Refunds Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTerminalRefundsResponse());
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
     * @param TerminalRefund[]|null $value
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
     * Initializes a new Search Terminal Refunds Response object.
     */
    public function build(): SearchTerminalRefundsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
