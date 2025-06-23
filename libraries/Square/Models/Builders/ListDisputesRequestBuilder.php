<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListDisputesRequest;

/**
 * Builder for model ListDisputesRequest
 *
 * @see ListDisputesRequest
 */
class ListDisputesRequestBuilder
{
    /**
     * @var ListDisputesRequest
     */
    private $instance;

    private function __construct(ListDisputesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Disputes Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListDisputesRequest());
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
     * Sets states field.
     *
     * @param string[]|null $value
     */
    public function states(?array $value): self
    {
        $this->instance->setStates($value);
        return $this;
    }

    /**
     * Unsets states field.
     */
    public function unsetStates(): self
    {
        $this->instance->unsetStates();
        return $this;
    }

    /**
     * Sets location id field.
     *
     * @param string|null $value
     */
    public function locationId(?string $value): self
    {
        $this->instance->setLocationId($value);
        return $this;
    }

    /**
     * Unsets location id field.
     */
    public function unsetLocationId(): self
    {
        $this->instance->unsetLocationId();
        return $this;
    }

    /**
     * Initializes a new List Disputes Request object.
     */
    public function build(): ListDisputesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
