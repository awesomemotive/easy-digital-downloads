<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest;

/**
 * Builder for model BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest
 *
 * @see BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest
 */
class BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequestBuilder
{
    /**
     * @var BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest
     */
    private $instance;

    private function __construct(
        BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest $instance
    ) {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Location Custom Attributes Request Location Custom Attribute Delete
     * Request Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest());
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
     * Initializes a new Bulk Delete Location Custom Attributes Request Location Custom Attribute Delete
     * Request object.
     */
    public function build(): BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
