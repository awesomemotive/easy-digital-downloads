<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchTerminalActionsRequest;
use EDD\Vendor\Square\Models\TerminalActionQuery;

/**
 * Builder for model SearchTerminalActionsRequest
 *
 * @see SearchTerminalActionsRequest
 */
class SearchTerminalActionsRequestBuilder
{
    /**
     * @var SearchTerminalActionsRequest
     */
    private $instance;

    private function __construct(SearchTerminalActionsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Terminal Actions Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTerminalActionsRequest());
    }

    /**
     * Sets query field.
     *
     * @param TerminalActionQuery|null $value
     */
    public function query(?TerminalActionQuery $value): self
    {
        $this->instance->setQuery($value);
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
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
    }

    /**
     * Initializes a new Search Terminal Actions Request object.
     */
    public function build(): SearchTerminalActionsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
