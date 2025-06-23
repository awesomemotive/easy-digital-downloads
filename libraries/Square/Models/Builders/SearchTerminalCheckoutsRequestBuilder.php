<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchTerminalCheckoutsRequest;
use EDD\Vendor\Square\Models\TerminalCheckoutQuery;

/**
 * Builder for model SearchTerminalCheckoutsRequest
 *
 * @see SearchTerminalCheckoutsRequest
 */
class SearchTerminalCheckoutsRequestBuilder
{
    /**
     * @var SearchTerminalCheckoutsRequest
     */
    private $instance;

    private function __construct(SearchTerminalCheckoutsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Terminal Checkouts Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTerminalCheckoutsRequest());
    }

    /**
     * Sets query field.
     *
     * @param TerminalCheckoutQuery|null $value
     */
    public function query(?TerminalCheckoutQuery $value): self
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
     * Initializes a new Search Terminal Checkouts Request object.
     */
    public function build(): SearchTerminalCheckoutsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
