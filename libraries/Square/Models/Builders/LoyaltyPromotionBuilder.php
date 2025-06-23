<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyPromotion;
use EDD\Vendor\Square\Models\LoyaltyPromotionAvailableTimeData;
use EDD\Vendor\Square\Models\LoyaltyPromotionIncentive;
use EDD\Vendor\Square\Models\LoyaltyPromotionTriggerLimit;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model LoyaltyPromotion
 *
 * @see LoyaltyPromotion
 */
class LoyaltyPromotionBuilder
{
    /**
     * @var LoyaltyPromotion
     */
    private $instance;

    private function __construct(LoyaltyPromotion $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Promotion Builder object.
     *
     * @param string $name
     * @param LoyaltyPromotionIncentive $incentive
     * @param LoyaltyPromotionAvailableTimeData $availableTime
     */
    public static function init(
        string $name,
        LoyaltyPromotionIncentive $incentive,
        LoyaltyPromotionAvailableTimeData $availableTime
    ): self {
        return new self(new LoyaltyPromotion($name, $incentive, $availableTime));
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets trigger limit field.
     *
     * @param LoyaltyPromotionTriggerLimit|null $value
     */
    public function triggerLimit(?LoyaltyPromotionTriggerLimit $value): self
    {
        $this->instance->setTriggerLimit($value);
        return $this;
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
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets canceled at field.
     *
     * @param string|null $value
     */
    public function canceledAt(?string $value): self
    {
        $this->instance->setCanceledAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets loyalty program id field.
     *
     * @param string|null $value
     */
    public function loyaltyProgramId(?string $value): self
    {
        $this->instance->setLoyaltyProgramId($value);
        return $this;
    }

    /**
     * Sets minimum spend amount money field.
     *
     * @param Money|null $value
     */
    public function minimumSpendAmountMoney(?Money $value): self
    {
        $this->instance->setMinimumSpendAmountMoney($value);
        return $this;
    }

    /**
     * Sets qualifying item variation ids field.
     *
     * @param string[]|null $value
     */
    public function qualifyingItemVariationIds(?array $value): self
    {
        $this->instance->setQualifyingItemVariationIds($value);
        return $this;
    }

    /**
     * Unsets qualifying item variation ids field.
     */
    public function unsetQualifyingItemVariationIds(): self
    {
        $this->instance->unsetQualifyingItemVariationIds();
        return $this;
    }

    /**
     * Sets qualifying category ids field.
     *
     * @param string[]|null $value
     */
    public function qualifyingCategoryIds(?array $value): self
    {
        $this->instance->setQualifyingCategoryIds($value);
        return $this;
    }

    /**
     * Unsets qualifying category ids field.
     */
    public function unsetQualifyingCategoryIds(): self
    {
        $this->instance->unsetQualifyingCategoryIds();
        return $this;
    }

    /**
     * Initializes a new Loyalty Promotion object.
     */
    public function build(): LoyaltyPromotion
    {
        return CoreHelper::clone($this->instance);
    }
}
