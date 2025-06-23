<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListEventTypesRequest;

/**
 * Builder for model ListEventTypesRequest
 *
 * @see ListEventTypesRequest
 */
class ListEventTypesRequestBuilder
{
    /**
     * @var ListEventTypesRequest
     */
    private $instance;

    private function __construct(ListEventTypesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Event Types Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListEventTypesRequest());
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
     * Initializes a new List Event Types Request object.
     */
    public function build(): ListEventTypesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
