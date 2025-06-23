<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchRetrieveInventoryChangesRequest;

/**
 * Builder for model BatchRetrieveInventoryChangesRequest
 *
 * @see BatchRetrieveInventoryChangesRequest
 */
class BatchRetrieveInventoryChangesRequestBuilder
{
    /**
     * @var BatchRetrieveInventoryChangesRequest
     */
    private $instance;

    private function __construct(BatchRetrieveInventoryChangesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Retrieve Inventory Changes Request Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchRetrieveInventoryChangesRequest());
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
     * Sets types field.
     *
     * @param string[]|null $value
     */
    public function types(?array $value): self
    {
        $this->instance->setTypes($value);
        return $this;
    }

    /**
     * Unsets types field.
     */
    public function unsetTypes(): self
    {
        $this->instance->unsetTypes();
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
     * Sets updated before field.
     *
     * @param string|null $value
     */
    public function updatedBefore(?string $value): self
    {
        $this->instance->setUpdatedBefore($value);
        return $this;
    }

    /**
     * Unsets updated before field.
     */
    public function unsetUpdatedBefore(): self
    {
        $this->instance->unsetUpdatedBefore();
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
     * Initializes a new Batch Retrieve Inventory Changes Request object.
     */
    public function build(): BatchRetrieveInventoryChangesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
