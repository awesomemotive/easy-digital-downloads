<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteMerchantCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteMerchantCustomAttributeDefinitionResponse
 *
 * @see DeleteMerchantCustomAttributeDefinitionResponse
 */
class DeleteMerchantCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var DeleteMerchantCustomAttributeDefinitionResponse
     */
    private $instance;

    private function __construct(DeleteMerchantCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Merchant Custom Attribute Definition Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteMerchantCustomAttributeDefinitionResponse());
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
     * Initializes a new Delete Merchant Custom Attribute Definition Response object.
     */
    public function build(): DeleteMerchantCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
