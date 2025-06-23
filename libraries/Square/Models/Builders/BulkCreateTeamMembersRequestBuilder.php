<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkCreateTeamMembersRequest;
use EDD\Vendor\Square\Models\CreateTeamMemberRequest;

/**
 * Builder for model BulkCreateTeamMembersRequest
 *
 * @see BulkCreateTeamMembersRequest
 */
class BulkCreateTeamMembersRequestBuilder
{
    /**
     * @var BulkCreateTeamMembersRequest
     */
    private $instance;

    private function __construct(BulkCreateTeamMembersRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Create Team Members Request Builder object.
     *
     * @param array<string,CreateTeamMemberRequest> $teamMembers
     */
    public static function init(array $teamMembers): self
    {
        return new self(new BulkCreateTeamMembersRequest($teamMembers));
    }

    /**
     * Initializes a new Bulk Create Team Members Request object.
     */
    public function build(): BulkCreateTeamMembersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
