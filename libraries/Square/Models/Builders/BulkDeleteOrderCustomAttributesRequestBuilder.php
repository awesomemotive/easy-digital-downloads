<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteOrderCustomAttributesRequest;
use EDD\Vendor\Square\Models\BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute;

/**
 * Builder for model BulkDeleteOrderCustomAttributesRequest
 *
 * @see BulkDeleteOrderCustomAttributesRequest
 */
class BulkDeleteOrderCustomAttributesRequestBuilder
{
    /**
     * @var BulkDeleteOrderCustomAttributesRequest
     */
    private $instance;

    private function __construct(BulkDeleteOrderCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Order Custom Attributes Request Builder object.
     *
     * @param array<string,BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute> $values
     */
    public static function init(array $values): self
    {
        return new self(new BulkDeleteOrderCustomAttributesRequest($values));
    }

    /**
     * Initializes a new Bulk Delete Order Custom Attributes Request object.
     */
    public function build(): BulkDeleteOrderCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
