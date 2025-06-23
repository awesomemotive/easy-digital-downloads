<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchLoyaltyRewardsRequest;
use EDD\Vendor\Square\Models\SearchLoyaltyRewardsRequestLoyaltyRewardQuery;

/**
 * Builder for model SearchLoyaltyRewardsRequest
 *
 * @see SearchLoyaltyRewardsRequest
 */
class SearchLoyaltyRewardsRequestBuilder
{
    /**
     * @var SearchLoyaltyRewardsRequest
     */
    private $instance;

    private function __construct(SearchLoyaltyRewardsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Loyalty Rewards Request Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchLoyaltyRewardsRequest());
    }

    /**
     * Sets query field.
     *
     * @param SearchLoyaltyRewardsRequestLoyaltyRewardQuery|null $value
     */
    public function query(?SearchLoyaltyRewardsRequestLoyaltyRewardQuery $value): self
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
     * Initializes a new Search Loyalty Rewards Request object.
     */
    public function build(): SearchLoyaltyRewardsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
