<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerTextFilter;

/**
 * Builder for model CustomerTextFilter
 *
 * @see CustomerTextFilter
 */
class CustomerTextFilterBuilder
{
    /**
     * @var CustomerTextFilter
     */
    private $instance;

    private function __construct(CustomerTextFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Text Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomerTextFilter());
    }

    /**
     * Sets exact field.
     *
     * @param string|null $value
     */
    public function exact(?string $value): self
    {
        $this->instance->setExact($value);
        return $this;
    }

    /**
     * Unsets exact field.
     */
    public function unsetExact(): self
    {
        $this->instance->unsetExact();
        return $this;
    }

    /**
     * Sets fuzzy field.
     *
     * @param string|null $value
     */
    public function fuzzy(?string $value): self
    {
        $this->instance->setFuzzy($value);
        return $this;
    }

    /**
     * Unsets fuzzy field.
     */
    public function unsetFuzzy(): self
    {
        $this->instance->unsetFuzzy();
        return $this;
    }

    /**
     * Initializes a new Customer Text Filter object.
     */
    public function build(): CustomerTextFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
