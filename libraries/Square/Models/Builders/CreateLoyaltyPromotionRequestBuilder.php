<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateLoyaltyPromotionRequest;
use EDD\Vendor\Square\Models\LoyaltyPromotion;

/**
 * Builder for model CreateLoyaltyPromotionRequest
 *
 * @see CreateLoyaltyPromotionRequest
 */
class CreateLoyaltyPromotionRequestBuilder
{
    /**
     * @var CreateLoyaltyPromotionRequest
     */
    private $instance;

    private function __construct(CreateLoyaltyPromotionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Loyalty Promotion Request Builder object.
     *
     * @param LoyaltyPromotion $loyaltyPromotion
     * @param string $idempotencyKey
     */
    public static function init(LoyaltyPromotion $loyaltyPromotion, string $idempotencyKey): self
    {
        return new self(new CreateLoyaltyPromotionRequest($loyaltyPromotion, $idempotencyKey));
    }

    /**
     * Initializes a new Create Loyalty Promotion Request object.
     */
    public function build(): CreateLoyaltyPromotionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
