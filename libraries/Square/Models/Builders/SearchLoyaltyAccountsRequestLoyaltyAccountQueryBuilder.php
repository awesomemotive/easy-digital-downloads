<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyAccountMapping;
use EDD\Vendor\Square\Models\SearchLoyaltyAccountsRequestLoyaltyAccountQuery;

/**
 * Builder for model SearchLoyaltyAccountsRequestLoyaltyAccountQuery
 *
 * @see SearchLoyaltyAccountsRequestLoyaltyAccountQuery
 */
class SearchLoyaltyAccountsRequestLoyaltyAccountQueryBuilder
{
    /**
     * @var SearchLoyaltyAccountsRequestLoyaltyAccountQuery
     */
    private $instance;

    private function __construct(SearchLoyaltyAccountsRequestLoyaltyAccountQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Loyalty Accounts Request Loyalty Account Query Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchLoyaltyAccountsRequestLoyaltyAccountQuery());
    }

    /**
     * Sets mappings field.
     *
     * @param LoyaltyAccountMapping[]|null $value
     */
    public function mappings(?array $value): self
    {
        $this->instance->setMappings($value);
        return $this;
    }

    /**
     * Unsets mappings field.
     */
    public function unsetMappings(): self
    {
        $this->instance->unsetMappings();
        return $this;
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
     * Initializes a new Search Loyalty Accounts Request Loyalty Account Query object.
     */
    public function build(): SearchLoyaltyAccountsRequestLoyaltyAccountQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
