<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\EmployeeWage;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListEmployeeWagesResponse;

/**
 * Builder for model ListEmployeeWagesResponse
 *
 * @see ListEmployeeWagesResponse
 */
class ListEmployeeWagesResponseBuilder
{
    /**
     * @var ListEmployeeWagesResponse
     */
    private $instance;

    private function __construct(ListEmployeeWagesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Employee Wages Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListEmployeeWagesResponse());
    }

    /**
     * Sets employee wages field.
     *
     * @param EmployeeWage[]|null $value
     */
    public function employeeWages(?array $value): self
    {
        $this->instance->setEmployeeWages($value);
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
     * Initializes a new List Employee Wages Response object.
     */
    public function build(): ListEmployeeWagesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
