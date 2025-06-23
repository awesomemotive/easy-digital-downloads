<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchRetrieveOrdersRequest;

/**
 * Builder for model BatchRetrieveOrdersRequest
 *
 * @see BatchRetrieveOrdersRequest
 */
class BatchRetrieveOrdersRequestBuilder
{
    /**
     * @var BatchRetrieveOrdersRequest
     */
    private $instance;

    private function __construct(BatchRetrieveOrdersRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Retrieve Orders Request Builder object.
     *
     * @param string[] $orderIds
     */
    public static function init(array $orderIds): self
    {
        return new self(new BatchRetrieveOrdersRequest($orderIds));
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
     * Initializes a new Batch Retrieve Orders Request object.
     */
    public function build(): BatchRetrieveOrdersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
