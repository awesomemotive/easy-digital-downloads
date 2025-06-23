<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttribute;
use EDD\Vendor\Square\Models\UpsertOrderCustomAttributeRequest;

/**
 * Builder for model UpsertOrderCustomAttributeRequest
 *
 * @see UpsertOrderCustomAttributeRequest
 */
class UpsertOrderCustomAttributeRequestBuilder
{
    /**
     * @var UpsertOrderCustomAttributeRequest
     */
    private $instance;

    private function __construct(UpsertOrderCustomAttributeRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Upsert Order Custom Attribute Request Builder object.
     *
     * @param CustomAttribute $customAttribute
     */
    public static function init(CustomAttribute $customAttribute): self
    {
        return new self(new UpsertOrderCustomAttributeRequest($customAttribute));
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
     * Initializes a new Upsert Order Custom Attribute Request object.
     */
    public function build(): UpsertOrderCustomAttributeRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
