<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkCreateTeamMembersResponse;
use EDD\Vendor\Square\Models\CreateTeamMemberResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model BulkCreateTeamMembersResponse
 *
 * @see BulkCreateTeamMembersResponse
 */
class BulkCreateTeamMembersResponseBuilder
{
    /**
     * @var BulkCreateTeamMembersResponse
     */
    private $instance;

    private function __construct(BulkCreateTeamMembersResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Create Team Members Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkCreateTeamMembersResponse());
    }

    /**
     * Sets team members field.
     *
     * @param array<string,CreateTeamMemberResponse>|null $value
     */
    public function teamMembers(?array $value): self
    {
        $this->instance->setTeamMembers($value);
        return $this;
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Initializes a new Bulk Create Team Members Response object.
     */
    public function build(): BulkCreateTeamMembersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
