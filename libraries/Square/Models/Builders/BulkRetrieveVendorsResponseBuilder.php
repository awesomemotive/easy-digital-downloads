<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkRetrieveVendorsResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\RetrieveVendorResponse;

/**
 * Builder for model BulkRetrieveVendorsResponse
 *
 * @see BulkRetrieveVendorsResponse
 */
class BulkRetrieveVendorsResponseBuilder
{
    /**
     * @var BulkRetrieveVendorsResponse
     */
    private $instance;

    private function __construct(BulkRetrieveVendorsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Retrieve Vendors Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkRetrieveVendorsResponse());
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
     * Sets responses field.
     *
     * @param array<string,RetrieveVendorResponse>|null $value
     */
    public function responses(?array $value): self
    {
        $this->instance->setResponses($value);
        return $this;
    }

    /**
     * Initializes a new Bulk Retrieve Vendors Response object.
     */
    public function build(): BulkRetrieveVendorsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
