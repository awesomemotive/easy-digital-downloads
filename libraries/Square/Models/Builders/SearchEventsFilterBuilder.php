<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchEventsFilter;
use EDD\Vendor\Square\Models\TimeRange;

/**
 * Builder for model SearchEventsFilter
 *
 * @see SearchEventsFilter
 */
class SearchEventsFilterBuilder
{
    /**
     * @var SearchEventsFilter
     */
    private $instance;

    private function __construct(SearchEventsFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Events Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchEventsFilter());
    }

    /**
     * Sets event types field.
     *
     * @param string[]|null $value
     */
    public function eventTypes(?array $value): self
    {
        $this->instance->setEventTypes($value);
        return $this;
    }

    /**
     * Unsets event types field.
     */
    public function unsetEventTypes(): self
    {
        $this->instance->unsetEventTypes();
        return $this;
    }

    /**
     * Sets merchant ids field.
     *
     * @param string[]|null $value
     */
    public function merchantIds(?array $value): self
    {
        $this->instance->setMerchantIds($value);
        return $this;
    }

    /**
     * Unsets merchant ids field.
     */
    public function unsetMerchantIds(): self
    {
        $this->instance->unsetMerchantIds();
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
     * Sets created at field.
     *
     * @param TimeRange|null $value
     */
    public function createdAt(?TimeRange $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Search Events Filter object.
     */
    public function build(): SearchEventsFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
