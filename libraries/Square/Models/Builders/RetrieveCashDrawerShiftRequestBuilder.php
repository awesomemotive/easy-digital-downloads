<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RetrieveCashDrawerShiftRequest;

/**
 * Builder for model RetrieveCashDrawerShiftRequest
 *
 * @see RetrieveCashDrawerShiftRequest
 */
class RetrieveCashDrawerShiftRequestBuilder
{
    /**
     * @var RetrieveCashDrawerShiftRequest
     */
    private $instance;

    private function __construct(RetrieveCashDrawerShiftRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Cash Drawer Shift Request Builder object.
     *
     * @param string $locationId
     */
    public static function init(string $locationId): self
    {
        return new self(new RetrieveCashDrawerShiftRequest($locationId));
    }

    /**
     * Initializes a new Retrieve Cash Drawer Shift Request object.
     */
    public function build(): RetrieveCashDrawerShiftRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
