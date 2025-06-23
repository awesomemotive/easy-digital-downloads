<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchLoyaltyAccountsRequest;
use EDD\Vendor\Square\Models\SearchLoyaltyAccountsRequestLoyaltyAccountQuery;

/**
 * Builder for model SearchLoyaltyAccountsRequest
 *
 * @see SearchLoyaltyAccountsRequest
 */
class SearchLoyaltyAccountsRequestBuilder
{
    /**
     * @var SearchLoyaltyAccountsRequest
     */
    private $instance;

    private function __construct(SearchLoyaltyAccountsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Loyalty Accounts Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchLoyaltyAccountsRequest());
    }

    /**
     * Sets query field.
     *
     * @param SearchLoyaltyAccountsRequestLoyaltyAccountQuery|null $value
     */
    public function query(?SearchLoyaltyAccountsRequestLoyaltyAccountQuery $value): self
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
     * Initializes a new Search Loyalty Accounts Request object.
     */
    public function build(): SearchLoyaltyAccountsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
