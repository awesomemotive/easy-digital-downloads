<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchShiftsRequest;
use EDD\Vendor\Square\Models\ShiftQuery;

/**
 * Builder for model SearchShiftsRequest
 *
 * @see SearchShiftsRequest
 */
class SearchShiftsRequestBuilder
{
    /**
     * @var SearchShiftsRequest
     */
    private $instance;

    private function __construct(SearchShiftsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Shifts Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchShiftsRequest());
    }

    /**
     * Sets query field.
     *
     * @param ShiftQuery|null $value
     */
    public function query(?ShiftQuery $value): self
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
     * Initializes a new Search Shifts Request object.
     */
    public function build(): SearchShiftsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
