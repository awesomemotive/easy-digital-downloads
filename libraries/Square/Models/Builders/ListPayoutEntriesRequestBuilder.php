<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListPayoutEntriesRequest;

/**
 * Builder for model ListPayoutEntriesRequest
 *
 * @see ListPayoutEntriesRequest
 */
class ListPayoutEntriesRequestBuilder
{
    /**
     * @var ListPayoutEntriesRequest
     */
    private $instance;

    private function __construct(ListPayoutEntriesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payout Entries Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPayoutEntriesRequest());
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
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
     * Initializes a new List Payout Entries Request object.
     */
    public function build(): ListPayoutEntriesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
