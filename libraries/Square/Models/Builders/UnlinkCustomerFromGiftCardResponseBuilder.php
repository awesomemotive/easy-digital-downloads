<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\GiftCard;
use EDD\Vendor\Square\Models\UnlinkCustomerFromGiftCardResponse;

/**
 * Builder for model UnlinkCustomerFromGiftCardResponse
 *
 * @see UnlinkCustomerFromGiftCardResponse
 */
class UnlinkCustomerFromGiftCardResponseBuilder
{
    /**
     * @var UnlinkCustomerFromGiftCardResponse
     */
    private $instance;

    private function __construct(UnlinkCustomerFromGiftCardResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Unlink Customer From Gift Card Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UnlinkCustomerFromGiftCardResponse());
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
     * Sets gift card field.
     *
     * @param GiftCard|null $value
     */
    public function giftCard(?GiftCard $value): self
    {
        $this->instance->setGiftCard($value);
        return $this;
    }

    /**
     * Initializes a new Unlink Customer From Gift Card Response object.
     */
    public function build(): UnlinkCustomerFromGiftCardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
