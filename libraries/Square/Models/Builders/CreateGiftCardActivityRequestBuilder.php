<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateGiftCardActivityRequest;
use EDD\Vendor\Square\Models\GiftCardActivity;

/**
 * Builder for model CreateGiftCardActivityRequest
 *
 * @see CreateGiftCardActivityRequest
 */
class CreateGiftCardActivityRequestBuilder
{
    /**
     * @var CreateGiftCardActivityRequest
     */
    private $instance;

    private function __construct(CreateGiftCardActivityRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Gift Card Activity Request Builder object.
     *
     * @param string $idempotencyKey
     * @param GiftCardActivity $giftCardActivity
     */
    public static function init(string $idempotencyKey, GiftCardActivity $giftCardActivity): self
    {
        return new self(new CreateGiftCardActivityRequest($idempotencyKey, $giftCardActivity));
    }

    /**
     * Initializes a new Create Gift Card Activity Request object.
     */
    public function build(): CreateGiftCardActivityRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
