<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteMerchantCustomAttributesResponse;
use EDD\Vendor\Square\Models\BulkDeleteMerchantCustomAttributesResponseMerchantCustomAttributeDeleteResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkDeleteMerchantCustomAttributesResponse
 *
 * @see BulkDeleteMerchantCustomAttributesResponse
 */
class BulkDeleteMerchantCustomAttributesResponseBuilder
{
    /**
     * @var BulkDeleteMerchantCustomAttributesResponse
     */
    private $instance;

    private function __construct(BulkDeleteMerchantCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Merchant Custom Attributes Response Builder object.
     *
     * @param array<string,BulkDeleteMerchantCustomAttributesResponseMerchantCustomAttributeDeleteResponse> $values
     */
    public static function init(array $values): self
    {
        return new self(new BulkDeleteMerchantCustomAttributesResponse($values));
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
     * Initializes a new Bulk Delete Merchant Custom Attributes Response object.
     */
    public function build(): BulkDeleteMerchantCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
