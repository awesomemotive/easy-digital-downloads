<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Shift;
use EDD\Vendor\Square\Models\UpdateShiftRequest;

/**
 * Builder for model UpdateShiftRequest
 *
 * @see UpdateShiftRequest
 */
class UpdateShiftRequestBuilder
{
    /**
     * @var UpdateShiftRequest
     */
    private $instance;

    private function __construct(UpdateShiftRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Shift Request Builder object.
     *
     * @param Shift $shift
     */
    public static function init(Shift $shift): self
    {
        return new self(new UpdateShiftRequest($shift));
    }

    /**
     * Initializes a new Update Shift Request object.
     */
    public function build(): UpdateShiftRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
