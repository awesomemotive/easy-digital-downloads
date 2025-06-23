<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest;

/**
 * Builder for model BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest
 *
 * @see BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest
 */
class BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequestBuilder
{
    /**
     * @var BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest
     */
    private $instance;

    private function __construct(
        BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest $instance
    ) {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Merchant Custom Attributes Request Merchant Custom Attribute Delete
     * Request Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest());
    }

    /**
     * Sets key field.
     *
     * @param string|null $value
     */
    public function key(?string $value): self
    {
        $this->instance->setKey($value);
        return $this;
    }

    /**
     * Initializes a new Bulk Delete Merchant Custom Attributes Request Merchant Custom Attribute Delete
     * Request object.
     */
    public function build(): BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
