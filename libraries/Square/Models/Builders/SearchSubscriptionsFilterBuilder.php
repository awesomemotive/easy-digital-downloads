<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchSubscriptionsFilter;

/**
 * Builder for model SearchSubscriptionsFilter
 *
 * @see SearchSubscriptionsFilter
 */
class SearchSubscriptionsFilterBuilder
{
    /**
     * @var SearchSubscriptionsFilter
     */
    private $instance;

    private function __construct(SearchSubscriptionsFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Subscriptions Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchSubscriptionsFilter());
    }

    /**
     * Sets customer ids field.
     *
     * @param string[]|null $value
     */
    public function customerIds(?array $value): self
    {
        $this->instance->setCustomerIds($value);
        return $this;
    }

    /**
     * Unsets customer ids field.
     */
    public function unsetCustomerIds(): self
    {
        $this->instance->unsetCustomerIds();
        return $this;
    }

    /**
     * Sets location ids field.
     *
     * @param string[]|null $value
     */
    public function locationIds(?array $value): self
    {
        $this->instance->setLocationIds($value);
        return $this;
    }

    /**
     * Unsets location ids field.
     */
    public function unsetLocationIds(): self
    {
        $this->instance->unsetLocationIds();
        return $this;
    }

    /**
     * Sets source names field.
     *
     * @param string[]|null $value
     */
    public function sourceNames(?array $value): self
    {
        $this->instance->setSourceNames($value);
        return $this;
    }

    /**
     * Unsets source names field.
     */
    public function unsetSourceNames(): self
    {
        $this->instance->unsetSourceNames();
        return $this;
    }

    /**
     * Initializes a new Search Subscriptions Filter object.
     */
    public function build(): SearchSubscriptionsFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
