<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventQuery;
use EDD\Vendor\Square\Models\SearchLoyaltyEventsRequest;

/**
 * Builder for model SearchLoyaltyEventsRequest
 *
 * @see SearchLoyaltyEventsRequest
 */
class SearchLoyaltyEventsRequestBuilder
{
    /**
     * @var SearchLoyaltyEventsRequest
     */
    private $instance;

    private function __construct(SearchLoyaltyEventsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Loyalty Events Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchLoyaltyEventsRequest());
    }

    /**
     * Sets query field.
     *
     * @param LoyaltyEventQuery|null $value
     */
    public function query(?LoyaltyEventQuery $value): self
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
     * Initializes a new Search Loyalty Events Request object.
     */
    public function build(): SearchLoyaltyEventsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
