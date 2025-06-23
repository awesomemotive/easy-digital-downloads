<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelLoyaltyPromotionResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\LoyaltyPromotion;

/**
 * Builder for model CancelLoyaltyPromotionResponse
 *
 * @see CancelLoyaltyPromotionResponse
 */
class CancelLoyaltyPromotionResponseBuilder
{
    /**
     * @var CancelLoyaltyPromotionResponse
     */
    private $instance;

    private function __construct(CancelLoyaltyPromotionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Loyalty Promotion Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CancelLoyaltyPromotionResponse());
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
     * Sets loyalty promotion field.
     *
     * @param LoyaltyPromotion|null $value
     */
    public function loyaltyPromotion(?LoyaltyPromotion $value): self
    {
        $this->instance->setLoyaltyPromotion($value);
        return $this;
    }

    /**
     * Initializes a new Cancel Loyalty Promotion Response object.
     */
    public function build(): CancelLoyaltyPromotionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
