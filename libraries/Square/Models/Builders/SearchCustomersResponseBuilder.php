<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Customer;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchCustomersResponse;

/**
 * Builder for model SearchCustomersResponse
 *
 * @see SearchCustomersResponse
 */
class SearchCustomersResponseBuilder
{
    /**
     * @var SearchCustomersResponse
     */
    private $instance;

    private function __construct(SearchCustomersResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Customers Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchCustomersResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets customers field.
     *
     * @param Customer[]|null $value
     */
    public function customers(?array $value): self
    {
        $this->instance->setCustomers($value);
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
     * Sets count field.
     *
     * @param int|null $value
     */
    public function count(?int $value): self
    {
        $this->instance->setCount($value);
        return $this;
    }

    /**
     * Initializes a new Search Customers Response object.
     */
    public function build(): SearchCustomersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
