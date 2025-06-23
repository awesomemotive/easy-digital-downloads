<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListCustomerCustomAttributeDefinitionsRequest;

/**
 * Builder for model ListCustomerCustomAttributeDefinitionsRequest
 *
 * @see ListCustomerCustomAttributeDefinitionsRequest
 */
class ListCustomerCustomAttributeDefinitionsRequestBuilder
{
    /**
     * @var ListCustomerCustomAttributeDefinitionsRequest
     */
    private $instance;

    private function __construct(ListCustomerCustomAttributeDefinitionsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Customer Custom Attribute Definitions Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCustomerCustomAttributeDefinitionsRequest());
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
     * Initializes a new List Customer Custom Attribute Definitions Request object.
     */
    public function build(): ListCustomerCustomAttributeDefinitionsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
