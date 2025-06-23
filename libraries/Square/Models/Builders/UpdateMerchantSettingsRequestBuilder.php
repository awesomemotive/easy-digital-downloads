<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutMerchantSettings;
use EDD\Vendor\Square\Models\UpdateMerchantSettingsRequest;

/**
 * Builder for model UpdateMerchantSettingsRequest
 *
 * @see UpdateMerchantSettingsRequest
 */
class UpdateMerchantSettingsRequestBuilder
{
    /**
     * @var UpdateMerchantSettingsRequest
     */
    private $instance;

    private function __construct(UpdateMerchantSettingsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Merchant Settings Request Builder object.
     *
     * @param CheckoutMerchantSettings $merchantSettings
     */
    public static function init(CheckoutMerchantSettings $merchantSettings): self
    {
        return new self(new UpdateMerchantSettingsRequest($merchantSettings));
    }

    /**
     * Initializes a new Update Merchant Settings Request object.
     */
    public function build(): UpdateMerchantSettingsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
