<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TerminalRefundQuery;
use EDD\Vendor\Square\Models\TerminalRefundQueryFilter;
use EDD\Vendor\Square\Models\TerminalRefundQuerySort;

/**
 * Builder for model TerminalRefundQuery
 *
 * @see TerminalRefundQuery
 */
class TerminalRefundQueryBuilder
{
    /**
     * @var TerminalRefundQuery
     */
    private $instance;

    private function __construct(TerminalRefundQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Refund Query Builder object.
     */
    public static function init(): self
    {
        return new self(new TerminalRefundQuery());
    }

    /**
     * Sets filter field.
     *
     * @param TerminalRefundQueryFilter|null $value
     */
    public function filter(?TerminalRefundQueryFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param TerminalRefundQuerySort|null $value
     */
    public function sort(?TerminalRefundQuerySort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Terminal Refund Query object.
     */
    public function build(): TerminalRefundQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
