<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LinkCustomerToGiftCardRequest;

/**
 * Builder for model LinkCustomerToGiftCardRequest
 *
 * @see LinkCustomerToGiftCardRequest
 */
class LinkCustomerToGiftCardRequestBuilder
{
    /**
     * @var LinkCustomerToGiftCardRequest
     */
    private $instance;

    private function __construct(LinkCustomerToGiftCardRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Link Customer To Gift Card Request Builder object.
     *
     * @param string $customerId
     */
    public static function init(string $customerId): self
    {
        return new self(new LinkCustomerToGiftCardRequest($customerId));
    }

    /**
     * Initializes a new Link Customer To Gift Card Request object.
     */
    public function build(): LinkCustomerToGiftCardRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
