<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest;
use EDD\Vendor\Square\Models\CustomAttribute;

/**
 * Builder for model BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest
 *
 * @see BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest
 */
class BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequestBuilder
{
    /**
     * @var BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest
     */
    private $instance;

    private function __construct(
        BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest $instance
    ) {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Upsert Location Custom Attributes Request Location Custom Attribute Upsert
     * Request Builder object.
     *
     * @param string $locationId
     * @param CustomAttribute $customAttribute
     */
    public static function init(string $locationId, CustomAttribute $customAttribute): self
    {
        return new self(new BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest(
            $locationId,
            $customAttribute
        ));
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
     * Initializes a new Bulk Upsert Location Custom Attributes Request Location Custom Attribute Upsert
     * Request object.
     */
    public function build(): BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
