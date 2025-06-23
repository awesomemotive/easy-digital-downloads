<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateLoyaltyAccountRequest;
use EDD\Vendor\Square\Models\LoyaltyAccount;

/**
 * Builder for model CreateLoyaltyAccountRequest
 *
 * @see CreateLoyaltyAccountRequest
 */
class CreateLoyaltyAccountRequestBuilder
{
    /**
     * @var CreateLoyaltyAccountRequest
     */
    private $instance;

    private function __construct(CreateLoyaltyAccountRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Loyalty Account Request Builder object.
     *
     * @param LoyaltyAccount $loyaltyAccount
     * @param string $idempotencyKey
     */
    public static function init(LoyaltyAccount $loyaltyAccount, string $idempotencyKey): self
    {
        return new self(new CreateLoyaltyAccountRequest($loyaltyAccount, $idempotencyKey));
    }

    /**
     * Initializes a new Create Loyalty Account Request object.
     */
    public function build(): CreateLoyaltyAccountRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
