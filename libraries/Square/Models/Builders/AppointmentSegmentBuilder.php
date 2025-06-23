<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\AppointmentSegment;

/**
 * Builder for model AppointmentSegment
 *
 * @see AppointmentSegment
 */
class AppointmentSegmentBuilder
{
    /**
     * @var AppointmentSegment
     */
    private $instance;

    private function __construct(AppointmentSegment $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Appointment Segment Builder object.
     *
     * @param string $teamMemberId
     */
    public static function init(string $teamMemberId): self
    {
        return new self(new AppointmentSegment($teamMemberId));
    }

    /**
     * Sets duration minutes field.
     *
     * @param int|null $value
     */
    public function durationMinutes(?int $value): self
    {
        $this->instance->setDurationMinutes($value);
        return $this;
    }

    /**
     * Unsets duration minutes field.
     */
    public function unsetDurationMinutes(): self
    {
        $this->instance->unsetDurationMinutes();
        return $this;
    }

    /**
     * Sets service variation id field.
     *
     * @param string|null $value
     */
    public function serviceVariationId(?string $value): self
    {
        $this->instance->setServiceVariationId($value);
        return $this;
    }

    /**
     * Unsets service variation id field.
     */
    public function unsetServiceVariationId(): self
    {
        $this->instance->unsetServiceVariationId();
        return $this;
    }

    /**
     * Sets service variation version field.
     *
     * @param int|null $value
     */
    public function serviceVariationVersion(?int $value): self
    {
        $this->instance->setServiceVariationVersion($value);
        return $this;
    }

    /**
     * Unsets service variation version field.
     */
    public function unsetServiceVariationVersion(): self
    {
        $this->instance->unsetServiceVariationVersion();
        return $this;
    }

    /**
     * Sets intermission minutes field.
     *
     * @param int|null $value
     */
    public function intermissionMinutes(?int $value): self
    {
        $this->instance->setIntermissionMinutes($value);
        return $this;
    }

    /**
     * Sets any team member field.
     *
     * @param bool|null $value
     */
    public function anyTeamMember(?bool $value): self
    {
        $this->instance->setAnyTeamMember($value);
        return $this;
    }

    /**
     * Sets resource ids field.
     *
     * @param string[]|null $value
     */
    public function resourceIds(?array $value): self
    {
        $this->instance->setResourceIds($value);
        return $this;
    }

    /**
     * Initializes a new Appointment Segment object.
     */
    public function build(): AppointmentSegment
    {
        return CoreHelper::clone($this->instance);
    }
}
