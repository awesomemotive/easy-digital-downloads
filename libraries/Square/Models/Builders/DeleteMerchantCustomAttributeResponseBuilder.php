<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteMerchantCustomAttributeResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteMerchantCustomAttributeResponse
 *
 * @see DeleteMerchantCustomAttributeResponse
 */
class DeleteMerchantCustomAttributeResponseBuilder
{
    /**
     * @var DeleteMerchantCustomAttributeResponse
     */
    private $instance;

    private function __construct(DeleteMerchantCustomAttributeResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Merchant Custom Attribute Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteMerchantCustomAttributeResponse());
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
     * Initializes a new Delete Merchant Custom Attribute Response object.
     */
    public function build(): DeleteMerchantCustomAttributeResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
