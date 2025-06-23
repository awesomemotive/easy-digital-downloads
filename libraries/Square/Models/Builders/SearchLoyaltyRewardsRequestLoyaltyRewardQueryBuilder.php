<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchLoyaltyRewardsRequestLoyaltyRewardQuery;

/**
 * Builder for model SearchLoyaltyRewardsRequestLoyaltyRewardQuery
 *
 * @see SearchLoyaltyRewardsRequestLoyaltyRewardQuery
 */
class SearchLoyaltyRewardsRequestLoyaltyRewardQueryBuilder
{
    /**
     * @var SearchLoyaltyRewardsRequestLoyaltyRewardQuery
     */
    private $instance;

    private function __construct(SearchLoyaltyRewardsRequestLoyaltyRewardQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Loyalty Rewards Request Loyalty Reward Query Builder object.
     *
     * @param string $loyaltyAccountId
     */
    public static function init(string $loyaltyAccountId): self
    {
        return new self(new SearchLoyaltyRewardsRequestLoyaltyRewardQuery($loyaltyAccountId));
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Initializes a new Search Loyalty Rewards Request Loyalty Reward Query object.
     */
    public function build(): SearchLoyaltyRewardsRequestLoyaltyRewardQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
