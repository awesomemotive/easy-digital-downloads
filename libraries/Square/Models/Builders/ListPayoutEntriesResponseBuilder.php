<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListPayoutEntriesResponse;
use EDD\Vendor\Square\Models\PayoutEntry;

/**
 * Builder for model ListPayoutEntriesResponse
 *
 * @see ListPayoutEntriesResponse
 */
class ListPayoutEntriesResponseBuilder
{
    /**
     * @var ListPayoutEntriesResponse
     */
    private $instance;

    private function __construct(ListPayoutEntriesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Payout Entries Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListPayoutEntriesResponse());
    }

    /**
     * Sets payout entries field.
     *
     * @param PayoutEntry[]|null $value
     */
    public function payoutEntries(?array $value): self
    {
        $this->instance->setPayoutEntries($value);
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
     * Initializes a new List Payout Entries Response object.
     */
    public function build(): ListPayoutEntriesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
