<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\LoyaltyReward;
use EDD\Vendor\Square\Models\RetrieveLoyaltyRewardResponse;

/**
 * Builder for model RetrieveLoyaltyRewardResponse
 *
 * @see RetrieveLoyaltyRewardResponse
 */
class RetrieveLoyaltyRewardResponseBuilder
{
    /**
     * @var RetrieveLoyaltyRewardResponse
     */
    private $instance;

    private function __construct(RetrieveLoyaltyRewardResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Loyalty Reward Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveLoyaltyRewardResponse());
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
     * Sets reward field.
     *
     * @param LoyaltyReward|null $value
     */
    public function reward(?LoyaltyReward $value): self
    {
        $this->instance->setReward($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Loyalty Reward Response object.
     */
    public function build(): RetrieveLoyaltyRewardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
