<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteLocationCustomAttributesResponse;
use EDD\Vendor\Square\Models\BulkDeleteLocationCustomAttributesResponseLocationCustomAttributeDeleteResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkDeleteLocationCustomAttributesResponse
 *
 * @see BulkDeleteLocationCustomAttributesResponse
 */
class BulkDeleteLocationCustomAttributesResponseBuilder
{
    /**
     * @var BulkDeleteLocationCustomAttributesResponse
     */
    private $instance;

    private function __construct(BulkDeleteLocationCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Location Custom Attributes Response Builder object.
     *
     * @param array<string,BulkDeleteLocationCustomAttributesResponseLocationCustomAttributeDeleteResponse> $values
     */
    public static function init(array $values): self
    {
        return new self(new BulkDeleteLocationCustomAttributesResponse($values));
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
     * Initializes a new Bulk Delete Location Custom Attributes Response object.
     */
    public function build(): BulkDeleteLocationCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
