<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateTeamMemberRequest;
use EDD\Vendor\Square\Models\TeamMember;

/**
 * Builder for model CreateTeamMemberRequest
 *
 * @see CreateTeamMemberRequest
 */
class CreateTeamMemberRequestBuilder
{
    /**
     * @var CreateTeamMemberRequest
     */
    private $instance;

    private function __construct(CreateTeamMemberRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Team Member Request Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateTeamMemberRequest());
    }

    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value): self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
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
     * Initializes a new Create Team Member Request object.
     */
    public function build(): CreateTeamMemberRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
