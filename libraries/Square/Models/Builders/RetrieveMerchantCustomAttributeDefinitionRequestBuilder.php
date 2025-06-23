<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RetrieveMerchantCustomAttributeDefinitionRequest;

/**
 * Builder for model RetrieveMerchantCustomAttributeDefinitionRequest
 *
 * @see RetrieveMerchantCustomAttributeDefinitionRequest
 */
class RetrieveMerchantCustomAttributeDefinitionRequestBuilder
{
    /**
     * @var RetrieveMerchantCustomAttributeDefinitionRequest
     */
    private $instance;

    private function __construct(RetrieveMerchantCustomAttributeDefinitionRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Merchant Custom Attribute Definition Request Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveMerchantCustomAttributeDefinitionRequest());
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Merchant Custom Attribute Definition Request object.
     */
    public function build(): RetrieveMerchantCustomAttributeDefinitionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
