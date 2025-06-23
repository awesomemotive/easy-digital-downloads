<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListEmployeesRequest;

/**
 * Builder for model ListEmployeesRequest
 *
 * @see ListEmployeesRequest
 */
class ListEmployeesRequestBuilder
{
    /**
     * @var ListEmployeesRequest
     */
    private $instance;

    private function __construct(ListEmployeesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Employees Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListEmployeesRequest());
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
     * Initializes a new List Employees Request object.
     */
    public function build(): ListEmployeesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
