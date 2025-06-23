<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListTeamMemberWagesRequest;

/**
 * Builder for model ListTeamMemberWagesRequest
 *
 * @see ListTeamMemberWagesRequest
 */
class ListTeamMemberWagesRequestBuilder
{
    /**
     * @var ListTeamMemberWagesRequest
     */
    private $instance;

    private function __construct(ListTeamMemberWagesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Team Member Wages Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListTeamMemberWagesRequest());
    }

    /**
     * Sets team member id field.
     *
     * @param string|null $value
     */
    public function teamMemberId(?string $value): self
    {
        $this->instance->setTeamMemberId($value);
        return $this;
    }

    /**
     * Unsets team member id field.
     */
    public function unsetTeamMemberId(): self
    {
        $this->instance->unsetTeamMemberId();
        return $this;
    }

    /**
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
    }

    /**
     * Unsets limit field.
     */
    public function unsetLimit(): self
    {
        $this->instance->unsetLimit();
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Initializes a new List Team Member Wages Request object.
     */
    public function build(): ListTeamMemberWagesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
