<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\EventData;

/**
 * Builder for model EventData
 *
 * @see EventData
 */
class EventDataBuilder
{
    /**
     * @var EventData
     */
    private $instance;

    private function __construct(EventData $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Event Data Builder object.
     */
    public static function init(): self
    {
        return new self(new EventData());
    }

    /**
     * Sets type field.
     *
     * @param string|null $value
     */
    public function type(?string $value): self
    {
        $this->instance->setType($value);
        return $this;
    }

    /**
     * Unsets type field.
     */
    public function unsetType(): self
    {
        $this->instance->unsetType();
        return $this;
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets deleted field.
     *
     * @param bool|null $value
     */
    public function deleted(?bool $value): self
    {
        $this->instance->setDeleted($value);
        return $this;
    }

    /**
     * Unsets deleted field.
     */
    public function unsetDeleted(): self
    {
        $this->instance->unsetDeleted();
        return $this;
    }

    /**
     * Sets object field.
     *
     * @param mixed $value
     */
    public function object($value): self
    {
        $this->instance->setObject($value);
        return $this;
    }

    /**
     * Unsets object field.
     */
    public function unsetObject(): self
    {
        $this->instance->unsetObject();
        return $this;
    }

    /**
     * Initializes a new Event Data object.
     */
    public function build(): EventData
    {
        return CoreHelper::clone($this->instance);
    }
}
