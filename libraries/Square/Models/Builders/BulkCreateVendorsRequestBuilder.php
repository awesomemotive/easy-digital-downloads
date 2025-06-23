<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkCreateVendorsRequest;
use EDD\Vendor\Square\Models\Vendor;

/**
 * Builder for model BulkCreateVendorsRequest
 *
 * @see BulkCreateVendorsRequest
 */
class BulkCreateVendorsRequestBuilder
{
    /**
     * @var BulkCreateVendorsRequest
     */
    private $instance;

    private function __construct(BulkCreateVendorsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Create Vendors Request Builder object.
     *
     * @param array<string,Vendor> $vendors
     */
    public static function init(array $vendors): self
    {
        return new self(new BulkCreateVendorsRequest($vendors));
    }

    /**
     * Initializes a new Bulk Create Vendors Request object.
     */
    public function build(): BulkCreateVendorsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
