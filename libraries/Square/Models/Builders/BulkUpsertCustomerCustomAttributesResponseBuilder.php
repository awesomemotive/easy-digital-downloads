<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkUpsertCustomerCustomAttributesResponse;
use EDD\Vendor\Square\Models\BulkUpsertCustomerCustomAttributesResponseCustomerCustomAttributeUpsertResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkUpsertCustomerCustomAttributesResponse
 *
 * @see BulkUpsertCustomerCustomAttributesResponse
 */
class BulkUpsertCustomerCustomAttributesResponseBuilder
{
    /**
     * @var BulkUpsertCustomerCustomAttributesResponse
     */
    private $instance;

    private function __construct(BulkUpsertCustomerCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Upsert Customer Custom Attributes Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkUpsertCustomerCustomAttributesResponse());
    }

    /**
     * Sets values field.
     *
     * @param array<string,BulkUpsertCustomerCustomAttributesResponseCustomerCustomAttributeUpsertResponse>|null $value
     */
    public function values(?array $value): self
    {
        $this->instance->setValues($value);
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
     * Initializes a new Bulk Upsert Customer Custom Attributes Response object.
     */
    public function build(): BulkUpsertCustomerCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
