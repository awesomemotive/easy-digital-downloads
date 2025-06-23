<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\UnlinkCustomerFromGiftCardRequest;

/**
 * Builder for model UnlinkCustomerFromGiftCardRequest
 *
 * @see UnlinkCustomerFromGiftCardRequest
 */
class UnlinkCustomerFromGiftCardRequestBuilder
{
    /**
     * @var UnlinkCustomerFromGiftCardRequest
     */
    private $instance;

    private function __construct(UnlinkCustomerFromGiftCardRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Unlink Customer From Gift Card Request Builder object.
     *
     * @param string $customerId
     */
    public static function init(string $customerId): self
    {
        return new self(new UnlinkCustomerFromGiftCardRequest($customerId));
    }

    /**
     * Initializes a new Unlink Customer From Gift Card Request object.
     */
    public function build(): UnlinkCustomerFromGiftCardRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
