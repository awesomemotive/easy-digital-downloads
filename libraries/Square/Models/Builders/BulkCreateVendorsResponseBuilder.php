<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkCreateVendorsResponse;
use EDD\Vendor\Square\Models\CreateVendorResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkCreateVendorsResponse
 *
 * @see BulkCreateVendorsResponse
 */
class BulkCreateVendorsResponseBuilder
{
    /**
     * @var BulkCreateVendorsResponse
     */
    private $instance;

    private function __construct(BulkCreateVendorsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Create Vendors Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkCreateVendorsResponse());
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
     * @param array<string,CreateVendorResponse>|null $value
     */
    public function responses(?array $value): self
    {
        $this->instance->setResponses($value);
        return $this;
    }

    /**
     * Initializes a new Bulk Create Vendors Response object.
     */
    public function build(): BulkCreateVendorsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
