<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CheckoutMerchantSettings;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpdateMerchantSettingsResponse;

/**
 * Builder for model UpdateMerchantSettingsResponse
 *
 * @see UpdateMerchantSettingsResponse
 */
class UpdateMerchantSettingsResponseBuilder
{
    /**
     * @var UpdateMerchantSettingsResponse
     */
    private $instance;

    private function __construct(UpdateMerchantSettingsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Merchant Settings Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateMerchantSettingsResponse());
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
     * Sets merchant settings field.
     *
     * @param CheckoutMerchantSettings|null $value
     */
    public function merchantSettings(?CheckoutMerchantSettings $value): self
    {
        $this->instance->setMerchantSettings($value);
        return $this;
    }

    /**
     * Initializes a new Update Merchant Settings Response object.
     */
    public function build(): UpdateMerchantSettingsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
