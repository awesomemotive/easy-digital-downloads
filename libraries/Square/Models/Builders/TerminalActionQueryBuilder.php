<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TerminalActionQuery;
use EDD\Vendor\Square\Models\TerminalActionQueryFilter;
use EDD\Vendor\Square\Models\TerminalActionQuerySort;

/**
 * Builder for model TerminalActionQuery
 *
 * @see TerminalActionQuery
 */
class TerminalActionQueryBuilder
{
    /**
     * @var TerminalActionQuery
     */
    private $instance;

    private function __construct(TerminalActionQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Terminal Action Query Builder object.
     */
    public static function init(): self
    {
        return new self(new TerminalActionQuery());
    }

    /**
     * Sets filter field.
     *
     * @param TerminalActionQueryFilter|null $value
     */
    public function filter(?TerminalActionQueryFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Sets sort field.
     *
     * @param TerminalActionQuerySort|null $value
     */
    public function sort(?TerminalActionQuerySort $value): self
    {
        $this->instance->setSort($value);
        return $this;
    }

    /**
     * Initializes a new Terminal Action Query object.
     */
    public function build(): TerminalActionQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
