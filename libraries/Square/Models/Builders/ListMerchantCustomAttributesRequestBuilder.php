<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListMerchantCustomAttributesRequest;

/**
 * Builder for model ListMerchantCustomAttributesRequest
 *
 * @see ListMerchantCustomAttributesRequest
 */
class ListMerchantCustomAttributesRequestBuilder
{
    /**
     * @var ListMerchantCustomAttributesRequest
     */
    private $instance;

    private function __construct(ListMerchantCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Merchant Custom Attributes Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListMerchantCustomAttributesRequest());
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
     * Initializes a new List Merchant Custom Attributes Request object.
     */
    public function build(): ListMerchantCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
