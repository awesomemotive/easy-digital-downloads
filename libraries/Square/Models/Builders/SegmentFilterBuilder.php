<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\FilterValue;
use EDD\Vendor\Square\Models\SegmentFilter;

/**
 * Builder for model SegmentFilter
 *
 * @see SegmentFilter
 */
class SegmentFilterBuilder
{
    /**
     * @var SegmentFilter
     */
    private $instance;

    private function __construct(SegmentFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Segment Filter Builder object.
     *
     * @param string $serviceVariationId
     */
    public static function init(string $serviceVariationId): self
    {
        return new self(new SegmentFilter($serviceVariationId));
    }

    /**
     * Sets team member id filter field.
     *
     * @param FilterValue|null $value
     */
    public function teamMemberIdFilter(?FilterValue $value): self
    {
        $this->instance->setTeamMemberIdFilter($value);
        return $this;
    }

    /**
     * Initializes a new Segment Filter object.
     */
    public function build(): SegmentFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
