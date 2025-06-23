<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TeamMember;
use EDD\Vendor\Square\Models\UpdateTeamMemberRequest;

/**
 * Builder for model UpdateTeamMemberRequest
 *
 * @see UpdateTeamMemberRequest
 */
class UpdateTeamMemberRequestBuilder
{
    /**
     * @var UpdateTeamMemberRequest
     */
    private $instance;

    private function __construct(UpdateTeamMemberRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Team Member Request Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateTeamMemberRequest());
    }

    /**
     * Sets team member field.
     *
     * @param TeamMember|null $value
     */
    public function teamMember(?TeamMember $value): self
    {
        $this->instance->setTeamMember($value);
        return $this;
    }

    /**
     * Initializes a new Update Team Member Request object.
     */
    public function build(): UpdateTeamMemberRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
