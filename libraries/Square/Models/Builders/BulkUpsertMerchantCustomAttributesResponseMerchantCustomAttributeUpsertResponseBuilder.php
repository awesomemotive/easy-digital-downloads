<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse
 *
 * @see BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse
 */
class BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponseBuilder
{
    /**
     * @var BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse
     */
    private $instance;

    private function __construct(
        BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse $instance
    ) {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Upsert Merchant Custom Attributes Response Merchant Custom Attribute Upsert
     * Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse());
    }

    /**
     * Sets merchant id field.
     *
     * @param string|null $value
     */
    public function merchantId(?string $value): self
    {
        $this->instance->setMerchantId($value);
        return $this;
    }

    /**
     * Sets custom attribute field.
     *
     * @param CustomAttribute|null $value
     */
    public function customAttribute(?CustomAttribute $value): self
    {
        $this->instance->setCustomAttribute($value);
        return $this;
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
     * Initializes a new Bulk Upsert Merchant Custom Attributes Response Merchant Custom Attribute Upsert
     * Response object.
     */
    public function build(): BulkUpsertMerchantCustomAttributesResponseMerchantCustomAttributeUpsertResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
