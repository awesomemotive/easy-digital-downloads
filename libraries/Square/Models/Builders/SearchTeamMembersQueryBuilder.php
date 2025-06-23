<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchTeamMembersFilter;
use EDD\Vendor\Square\Models\SearchTeamMembersQuery;

/**
 * Builder for model SearchTeamMembersQuery
 *
 * @see SearchTeamMembersQuery
 */
class SearchTeamMembersQueryBuilder
{
    /**
     * @var SearchTeamMembersQuery
     */
    private $instance;

    private function __construct(SearchTeamMembersQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Team Members Query Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTeamMembersQuery());
    }

    /**
     * Sets filter field.
     *
     * @param SearchTeamMembersFilter|null $value
     */
    public function filter(?SearchTeamMembersFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Initializes a new Search Team Members Query object.
     */
    public function build(): SearchTeamMembersQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
