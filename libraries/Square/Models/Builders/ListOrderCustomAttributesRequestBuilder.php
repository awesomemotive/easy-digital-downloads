<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListOrderCustomAttributesRequest;

/**
 * Builder for model ListOrderCustomAttributesRequest
 *
 * @see ListOrderCustomAttributesRequest
 */
class ListOrderCustomAttributesRequestBuilder
{
    /**
     * @var ListOrderCustomAttributesRequest
     */
    private $instance;

    private function __construct(ListOrderCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Order Custom Attributes Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListOrderCustomAttributesRequest());
    }

    /**
     * Sets visibility filter field.
     *
     * @param string|null $value
     */
    public function visibilityFilter(?string $value): self
    {
        $this->instance->setVisibilityFilter($value);
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
     * Sets with definitions field.
     *
     * @param bool|null $value
     */
    public function withDefinitions(?bool $value): self
    {
        $this->instance->setWithDefinitions($value);
        return $this;
    }

    /**
     * Unsets with definitions field.
     */
    public function unsetWithDefinitions(): self
    {
        $this->instance->unsetWithDefinitions();
        return $this;
    }

    /**
     * Initializes a new List Order Custom Attributes Request object.
     */
    public function build(): ListOrderCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
