<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BreakType;
use EDD\Vendor\Square\Models\UpdateBreakTypeRequest;

/**
 * Builder for model UpdateBreakTypeRequest
 *
 * @see UpdateBreakTypeRequest
 */
class UpdateBreakTypeRequestBuilder
{
    /**
     * @var UpdateBreakTypeRequest
     */
    private $instance;

    private function __construct(UpdateBreakTypeRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Break Type Request Builder object.
     *
     * @param BreakType $breakType
     */
    public static function init(BreakType $breakType): self
    {
        return new self(new UpdateBreakTypeRequest($breakType));
    }

    /**
     * Initializes a new Update Break Type Request object.
     */
    public function build(): UpdateBreakTypeRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
