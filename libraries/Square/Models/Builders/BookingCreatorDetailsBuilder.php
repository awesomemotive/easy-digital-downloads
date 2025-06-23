<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BookingCreatorDetails;

/**
 * Builder for model BookingCreatorDetails
 *
 * @see BookingCreatorDetails
 */
class BookingCreatorDetailsBuilder
{
    /**
     * @var BookingCreatorDetails
     */
    private $instance;

    private function __construct(BookingCreatorDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Booking Creator Details Builder object.
     */
    public static function init(): self
    {
        return new self(new BookingCreatorDetails());
    }

    /**
     * Sets creator type field.
     *
     * @param string|null $value
     */
    public function creatorType(?string $value): self
    {
        $this->instance->setCreatorType($value);
        return $this;
    }

    /**
     * Sets team member id field.
     *
     * @param string|null $value
     */
    public function teamMemberId(?string $value): self
    {
        $this->instance->setTeamMemberId($value);
        return $this;
    }

    /**
     * Sets customer id field.
     *
     * @param string|null $value
     */
    public function customerId(?string $value): self
    {
        $this->instance->setCustomerId($value);
        return $this;
    }

    /**
     * Initializes a new Booking Creator Details object.
     */
    public function build(): BookingCreatorDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
