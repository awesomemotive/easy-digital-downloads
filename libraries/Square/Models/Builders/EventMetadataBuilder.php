<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\EventMetadata;

/**
 * Builder for model EventMetadata
 *
 * @see EventMetadata
 */
class EventMetadataBuilder
{
    /**
     * @var EventMetadata
     */
    private $instance;

    private function __construct(EventMetadata $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Event Metadata Builder object.
     */
    public static function init(): self
    {
        return new self(new EventMetadata());
    }

    /**
     * Sets event id field.
     *
     * @param string|null $value
     */
    public function eventId(?string $value): self
    {
        $this->instance->setEventId($value);
        return $this;
    }

    /**
     * Unsets event id field.
     */
    public function unsetEventId(): self
    {
        $this->instance->unsetEventId();
        return $this;
    }

    /**
     * Sets api version field.
     *
     * @param string|null $value
     */
    public function apiVersion(?string $value): self
    {
        $this->instance->setApiVersion($value);
        return $this;
    }

    /**
     * Unsets api version field.
     */
    public function unsetApiVersion(): self
    {
        $this->instance->unsetApiVersion();
        return $this;
    }

    /**
     * Initializes a new Event Metadata object.
     */
    public function build(): EventMetadata
    {
        return CoreHelper::clone($this->instance);
    }
}
