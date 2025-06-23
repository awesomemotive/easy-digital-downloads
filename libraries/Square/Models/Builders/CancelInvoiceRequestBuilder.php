<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CancelInvoiceRequest;

/**
 * Builder for model CancelInvoiceRequest
 *
 * @see CancelInvoiceRequest
 */
class CancelInvoiceRequestBuilder
{
    /**
     * @var CancelInvoiceRequest
     */
    private $instance;

    private function __construct(CancelInvoiceRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cancel Invoice Request Builder object.
     *
     * @param int $version
     */
    public static function init(int $version): self
    {
        return new self(new CancelInvoiceRequest($version));
    }

    /**
     * Initializes a new Cancel Invoice Request object.
     */
    public function build(): CancelInvoiceRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
