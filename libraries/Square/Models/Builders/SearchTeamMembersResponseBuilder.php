<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchTeamMembersResponse;
use EDD\Vendor\Square\Models\TeamMember;

/**
 * Builder for model SearchTeamMembersResponse
 *
 * @see SearchTeamMembersResponse
 */
class SearchTeamMembersResponseBuilder
{
    /**
     * @var SearchTeamMembersResponse
     */
    private $instance;

    private function __construct(SearchTeamMembersResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Team Members Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTeamMembersResponse());
    }

    /**
     * Sets team members field.
     *
     * @param TeamMember[]|null $value
     */
    public function teamMembers(?array $value): self
    {
        $this->instance->setTeamMembers($value);
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
     * Initializes a new Search Team Members Response object.
     */
    public function build(): SearchTeamMembersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
