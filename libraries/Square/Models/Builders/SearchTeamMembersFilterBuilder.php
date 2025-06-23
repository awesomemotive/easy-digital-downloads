<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SearchTeamMembersFilter;

/**
 * Builder for model SearchTeamMembersFilter
 *
 * @see SearchTeamMembersFilter
 */
class SearchTeamMembersFilterBuilder
{
    /**
     * @var SearchTeamMembersFilter
     */
    private $instance;

    private function __construct(SearchTeamMembersFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Team Members Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTeamMembersFilter());
    }

    /**
     * Sets location ids field.
     *
     * @param string[]|null $value
     */
    public function locationIds(?array $value): self
    {
        $this->instance->setLocationIds($value);
        return $this;
    }

    /**
     * Unsets location ids field.
     */
    public function unsetLocationIds(): self
    {
        $this->instance->unsetLocationIds();
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets is owner field.
     *
     * @param bool|null $value
     */
    public function isOwner(?bool $value): self
    {
        $this->instance->setIsOwner($value);
        return $this;
    }

    /**
     * Unsets is owner field.
     */
    public function unsetIsOwner(): self
    {
        $this->instance->unsetIsOwner();
        return $this;
    }

    /**
     * Initializes a new Search Team Members Filter object.
     */
    public function build(): SearchTeamMembersFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
