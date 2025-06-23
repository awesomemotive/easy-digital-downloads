<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TerminalCheckoutQuery;
use EDD\Vendor\Square\Models\TerminalCheckoutQueryFilter;
use EDD\Vendor\Square\Models\TerminalCheckoutQuerySort;

/**
 * Builder for model TerminalCheckoutQuery
 *
 * @see TerminalCheckoutQuery
 */
class TerminalCheckoutQueryBuilder
{
    /**
     * @var TerminalCheckoutQuery
     */
    private $instance;

    private function __construct(TerminalCheckoutQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Checkout Query Builder object.
     */
    public static function init(): self
    {
        return new self(new TerminalCheckoutQuery());
    }

    /**
     * Sets filter field.
     *
     * @param TerminalCheckoutQueryFilter|null $value
     */
    public function filter(?TerminalCheckoutQueryFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param TerminalCheckoutQuerySort|null $value
     */
    public function sort(?TerminalCheckoutQuerySort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Terminal Checkout Query object.
     */
    public function build(): TerminalCheckoutQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
