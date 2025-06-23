<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchSubscriptionsQuery;
use EDD\Vendor\Square\Models\SearchSubscriptionsRequest;

/**
 * Builder for model SearchSubscriptionsRequest
 *
 * @see SearchSubscriptionsRequest
 */
class SearchSubscriptionsRequestBuilder
{
    /**
     * @var SearchSubscriptionsRequest
     */
    private $instance;

    private function __construct(SearchSubscriptionsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Subscriptions Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchSubscriptionsRequest());
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
     * @param SearchSubscriptionsQuery|null $value
     */
    public function query(?SearchSubscriptionsQuery $value): self
    {
        $this->instance->setQuery($value);
        return $this;
    }

    /**
     * Sets include field.
     *
     * @param string[]|null $value
     */
    public function include(?array $value): self
    {
        $this->instance->setInclude($value);
        return $this;
    }

    /**
     * Initializes a new Search Subscriptions Request object.
     */
    public function build(): SearchSubscriptionsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
