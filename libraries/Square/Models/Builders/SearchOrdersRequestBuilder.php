<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchOrdersQuery;
use EDD\Vendor\Square\Models\SearchOrdersRequest;

/**
 * Builder for model SearchOrdersRequest
 *
 * @see SearchOrdersRequest
 */
class SearchOrdersRequestBuilder
{
    /**
     * @var SearchOrdersRequest
     */
    private $instance;

    private function __construct(SearchOrdersRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Orders Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchOrdersRequest());
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
     * Sets query field.
     *
     * @param SearchOrdersQuery|null $value
     */
    public function query(?SearchOrdersQuery $value): self
    {
        $this->instance->setQuery($value);
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
     * Sets return entries field.
     *
     * @param bool|null $value
     */
    public function returnEntries(?bool $value): self
    {
        $this->instance->setReturnEntries($value);
        return $this;
    }

    /**
     * Initializes a new Search Orders Request object.
     */
    public function build(): SearchOrdersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
