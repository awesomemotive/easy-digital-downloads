<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchOrdersFulfillmentFilter;

/**
 * Builder for model SearchOrdersFulfillmentFilter
 *
 * @see SearchOrdersFulfillmentFilter
 */
class SearchOrdersFulfillmentFilterBuilder
{
    /**
     * @var SearchOrdersFulfillmentFilter
     */
    private $instance;

    private function __construct(SearchOrdersFulfillmentFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Orders Fulfillment Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchOrdersFulfillmentFilter());
    }

    /**
     * Sets fulfillment types field.
     *
     * @param string[]|null $value
     */
    public function fulfillmentTypes(?array $value): self
    {
        $this->instance->setFulfillmentTypes($value);
        return $this;
    }

    /**
     * Unsets fulfillment types field.
     */
    public function unsetFulfillmentTypes(): self
    {
        $this->instance->unsetFulfillmentTypes();
        return $this;
    }

    /**
     * Sets fulfillment states field.
     *
     * @param string[]|null $value
     */
    public function fulfillmentStates(?array $value): self
    {
        $this->instance->setFulfillmentStates($value);
        return $this;
    }

    /**
     * Unsets fulfillment states field.
     */
    public function unsetFulfillmentStates(): self
    {
        $this->instance->unsetFulfillmentStates();
        return $this;
    }

    /**
     * Initializes a new Search Orders Fulfillment Filter object.
     */
    public function build(): SearchOrdersFulfillmentFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
