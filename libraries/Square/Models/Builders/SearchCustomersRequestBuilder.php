<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerQuery;
use EDD\Vendor\Square\Models\SearchCustomersRequest;

/**
 * Builder for model SearchCustomersRequest
 *
 * @see SearchCustomersRequest
 */
class SearchCustomersRequestBuilder
{
    /**
     * @var SearchCustomersRequest
     */
    private $instance;

    private function __construct(SearchCustomersRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Customers Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchCustomersRequest());
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
     * @param CustomerQuery|null $value
     */
    public function query(?CustomerQuery $value): self
    {
        $this->instance->setQuery($value);
        return $this;
    }

    /**
     * Sets count field.
     *
     * @param bool|null $value
     */
    public function count(?bool $value): self
    {
        $this->instance->setCount($value);
        return $this;
    }

    /**
     * Initializes a new Search Customers Request object.
     */
    public function build(): SearchCustomersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
