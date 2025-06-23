<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\V1ListOrdersResponse;
use EDD\Vendor\Square\Models\V1Order;

/**
 * Builder for model V1ListOrdersResponse
 *
 * @see V1ListOrdersResponse
 */
class V1ListOrdersResponseBuilder
{
    /**
     * @var V1ListOrdersResponse
     */
    private $instance;

    private function __construct(V1ListOrdersResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 List Orders Response Builder object.
     */
    public static function init(): self
    {
        return new self(new V1ListOrdersResponse());
    }

    /**
     * Sets items field.
     *
     * @param V1Order[]|null $value
     */
    public function items(?array $value): self
    {
        $this->instance->setItems($value);
        return $this;
    }

    /**
     * Initializes a new V1 List Orders Response object.
     */
    public function build(): V1ListOrdersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
