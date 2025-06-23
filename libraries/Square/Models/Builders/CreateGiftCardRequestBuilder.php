<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateGiftCardRequest;
use EDD\Vendor\Square\Models\GiftCard;

/**
 * Builder for model CreateGiftCardRequest
 *
 * @see CreateGiftCardRequest
 */
class CreateGiftCardRequestBuilder
{
    /**
     * @var CreateGiftCardRequest
     */
    private $instance;

    private function __construct(CreateGiftCardRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Gift Card Request Builder object.
     *
     * @param string $idempotencyKey
     * @param string $locationId
     * @param GiftCard $giftCard
     */
    public static function init(string $idempotencyKey, string $locationId, GiftCard $giftCard): self
    {
        return new self(new CreateGiftCardRequest($idempotencyKey, $locationId, $giftCard));
    }

    /**
     * Initializes a new Create Gift Card Request object.
     */
    public function build(): CreateGiftCardRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
