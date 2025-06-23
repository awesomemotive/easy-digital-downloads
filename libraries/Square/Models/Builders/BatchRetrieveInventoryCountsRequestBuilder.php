<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchRetrieveInventoryCountsRequest;

/**
 * Builder for model BatchRetrieveInventoryCountsRequest
 *
 * @see BatchRetrieveInventoryCountsRequest
 */
class BatchRetrieveInventoryCountsRequestBuilder
{
    /**
     * @var BatchRetrieveInventoryCountsRequest
     */
    private $instance;

    private function __construct(BatchRetrieveInventoryCountsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Retrieve Inventory Counts Request Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchRetrieveInventoryCountsRequest());
    }

    /**
     * Sets catalog object ids field.
     *
     * @param string[]|null $value
     */
    public function catalogObjectIds(?array $value): self
    {
        $this->instance->setCatalogObjectIds($value);
        return $this;
    }

    /**
     * Unsets catalog object ids field.
     */
    public function unsetCatalogObjectIds(): self
    {
        $this->instance->unsetCatalogObjectIds();
        return $this;
    }

    /**
     * Sets location ids field.
     *
     * @param string[]|null $value
     */
    public function locationIds(?array $value): self
    {
        $this->instance->setLocationIds($value);
        return $this;
    }

    /**
     * Unsets location ids field.
     */
    public function unsetLocationIds(): self
    {
        $this->instance->unsetLocationIds();
        return $this;
    }

    /**
     * Sets updated after field.
     *
     * @param string|null $value
     */
    public function updatedAfter(?string $value): self
    {
        $this->instance->setUpdatedAfter($value);
        return $this;
    }

    /**
     * Unsets updated after field.
     */
    public function unsetUpdatedAfter(): self
    {
        $this->instance->unsetUpdatedAfter();
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
     * Sets states field.
     *
     * @param string[]|null $value
     */
    public function states(?array $value): self
    {
        $this->instance->setStates($value);
        return $this;
    }

    /**
     * Unsets states field.
     */
    public function unsetStates(): self
    {
        $this->instance->unsetStates();
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
     * Initializes a new Batch Retrieve Inventory Counts Request object.
     */
    public function build(): BatchRetrieveInventoryCountsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
