<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\UpsertMerchantCustomAttributeRequest;

/**
 * Builder for model UpsertMerchantCustomAttributeRequest
 *
 * @see UpsertMerchantCustomAttributeRequest
 */
class UpsertMerchantCustomAttributeRequestBuilder
{
    /**
     * @var UpsertMerchantCustomAttributeRequest
     */
    private $instance;

    private function __construct(UpsertMerchantCustomAttributeRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Upsert Merchant Custom Attribute Request Builder object.
     *
     * @param CustomAttribute $customAttribute
     */
    public static function init(CustomAttribute $customAttribute): self
    {
        return new self(new UpsertMerchantCustomAttributeRequest($customAttribute));
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }

    /**
     * Unsets idempotency key field.
     */
    public function unsetIdempotencyKey(): self
    {
        $this->instance->unsetIdempotencyKey();
        return $this;
    }

    /**
     * Initializes a new Upsert Merchant Custom Attribute Request object.
     */
    public function build(): UpsertMerchantCustomAttributeRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
