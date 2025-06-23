<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkUpsertLocationCustomAttributesRequest;
use EDD\Vendor\Square\Models\BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest;

/**
 * Builder for model BulkUpsertLocationCustomAttributesRequest
 *
 * @see BulkUpsertLocationCustomAttributesRequest
 */
class BulkUpsertLocationCustomAttributesRequestBuilder
{
    /**
     * @var BulkUpsertLocationCustomAttributesRequest
     */
    private $instance;

    private function __construct(BulkUpsertLocationCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Upsert Location Custom Attributes Request Builder object.
     *
     * @param array<string,BulkUpsertLocationCustomAttributesRequestLocationCustomAttributeUpsertRequest> $values
     */
    public static function init(array $values): self
    {
        return new self(new BulkUpsertLocationCustomAttributesRequest($values));
    }

    /**
     * Initializes a new Bulk Upsert Location Custom Attributes Request object.
     */
    public function build(): BulkUpsertLocationCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
