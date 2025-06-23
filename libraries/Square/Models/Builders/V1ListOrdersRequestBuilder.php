<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\V1ListOrdersRequest;

/**
 * Builder for model V1ListOrdersRequest
 *
 * @see V1ListOrdersRequest
 */
class V1ListOrdersRequestBuilder
{
    /**
     * @var V1ListOrdersRequest
     */
    private $instance;

    private function __construct(V1ListOrdersRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 List Orders Request Builder object.
     */
    public static function init(): self
    {
        return new self(new V1ListOrdersRequest());
    }

    /**
     * Sets order field.
     *
     * @param string|null $value
     */
    public function order(?string $value): self
    {
        $this->instance->setOrder($value);
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
     * Sets batch token field.
     *
     * @param string|null $value
     */
    public function batchToken(?string $value): self
    {
        $this->instance->setBatchToken($value);
        return $this;
    }

    /**
     * Unsets batch token field.
     */
    public function unsetBatchToken(): self
    {
        $this->instance->unsetBatchToken();
        return $this;
    }

    /**
     * Initializes a new V1 List Orders Request object.
     */
    public function build(): V1ListOrdersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
