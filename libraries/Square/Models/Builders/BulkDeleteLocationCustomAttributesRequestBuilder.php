<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteLocationCustomAttributesRequest;
use EDD\Vendor\Square\Models\BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest;

/**
 * Builder for model BulkDeleteLocationCustomAttributesRequest
 *
 * @see BulkDeleteLocationCustomAttributesRequest
 */
class BulkDeleteLocationCustomAttributesRequestBuilder
{
    /**
     * @var BulkDeleteLocationCustomAttributesRequest
     */
    private $instance;

    private function __construct(BulkDeleteLocationCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Location Custom Attributes Request Builder object.
     *
     * @param array<string,BulkDeleteLocationCustomAttributesRequestLocationCustomAttributeDeleteRequest> $values
     */
    public static function init(array $values): self
    {
        return new self(new BulkDeleteLocationCustomAttributesRequest($values));
    }

    /**
     * Initializes a new Bulk Delete Location Custom Attributes Request object.
     */
    public function build(): BulkDeleteLocationCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
