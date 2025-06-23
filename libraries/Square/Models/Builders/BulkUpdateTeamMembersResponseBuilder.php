<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkUpdateTeamMembersResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpdateTeamMemberResponse;

/**
 * Builder for model BulkUpdateTeamMembersResponse
 *
 * @see BulkUpdateTeamMembersResponse
 */
class BulkUpdateTeamMembersResponseBuilder
{
    /**
     * @var BulkUpdateTeamMembersResponse
     */
    private $instance;

    private function __construct(BulkUpdateTeamMembersResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Update Team Members Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BulkUpdateTeamMembersResponse());
    }

    /**
     * Sets team members field.
     *
     * @param array<string,UpdateTeamMemberResponse>|null $value
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
     * Initializes a new Bulk Update Team Members Response object.
     */
    public function build(): BulkUpdateTeamMembersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
