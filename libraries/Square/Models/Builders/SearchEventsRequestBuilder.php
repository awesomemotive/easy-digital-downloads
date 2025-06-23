<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchEventsQuery;
use EDD\Vendor\Square\Models\SearchEventsRequest;

/**
 * Builder for model SearchEventsRequest
 *
 * @see SearchEventsRequest
 */
class SearchEventsRequestBuilder
{
    /**
     * @var SearchEventsRequest
     */
    private $instance;

    private function __construct(SearchEventsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Events Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchEventsRequest());
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
     * Sets query field.
     *
     * @param SearchEventsQuery|null $value
     */
    public function query(?SearchEventsQuery $value): self
    {
        $this->instance->setQuery($value);
        return $this;
    }

    /**
     * Initializes a new Search Events Request object.
     */
    public function build(): SearchEventsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
