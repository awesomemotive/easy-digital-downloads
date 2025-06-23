<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute;
use EDD\Vendor\Square\Models\CustomAttribute;

/**
 * Builder for model BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute
 *
 * @see BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute
 */
class BulkUpsertOrderCustomAttributesRequestUpsertCustomAttributeBuilder
{
    /**
     * @var BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute
     */
    private $instance;

    private function __construct(BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Upsert Order Custom Attributes Request Upsert Custom Attribute Builder object.
     *
     * @param CustomAttribute $customAttribute
     * @param string $orderId
     */
    public static function init(CustomAttribute $customAttribute, string $orderId): self
    {
        return new self(
            new BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute($customAttribute, $orderId)
        );
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
     * Initializes a new Bulk Upsert Order Custom Attributes Request Upsert Custom Attribute object.
     */
    public function build(): BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute
    {
        return CoreHelper::clone($this->instance);
    }
}
