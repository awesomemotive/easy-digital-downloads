<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListCustomersRequest;

/**
 * Builder for model ListCustomersRequest
 *
 * @see ListCustomersRequest
 */
class ListCustomersRequestBuilder
{
    /**
     * @var ListCustomersRequest
     */
    private $instance;

    private function __construct(ListCustomersRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Customers Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCustomersRequest());
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
     * Sets sort field field.
     *
     * @param string|null $value
     */
    public function sortField(?string $value): self
    {
        $this->instance->setSortField($value);
        return $this;
    }

    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value): self
    {
        $this->instance->setSortOrder($value);
        return $this;
    }

    /**
     * Sets count field.
     *
     * @param bool|null $value
     */
    public function count(?bool $value): self
    {
        $this->instance->setCount($value);
        return $this;
    }

    /**
     * Unsets count field.
     */
    public function unsetCount(): self
    {
        $this->instance->unsetCount();
        return $this;
    }

    /**
     * Initializes a new List Customers Request object.
     */
    public function build(): ListCustomersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
