<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListEmployeeWagesRequest;

/**
 * Builder for model ListEmployeeWagesRequest
 *
 * @see ListEmployeeWagesRequest
 */
class ListEmployeeWagesRequestBuilder
{
    /**
     * @var ListEmployeeWagesRequest
     */
    private $instance;

    private function __construct(ListEmployeeWagesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Employee Wages Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListEmployeeWagesRequest());
    }

    /**
     * Sets employee id field.
     *
     * @param string|null $value
     */
    public function employeeId(?string $value): self
    {
        $this->instance->setEmployeeId($value);
        return $this;
    }

    /**
     * Unsets employee id field.
     */
    public function unsetEmployeeId(): self
    {
        $this->instance->unsetEmployeeId();
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
     * Initializes a new List Employee Wages Request object.
     */
    public function build(): ListEmployeeWagesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
