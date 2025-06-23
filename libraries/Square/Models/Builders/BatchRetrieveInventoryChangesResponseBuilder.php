<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchRetrieveInventoryChangesResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\InventoryChange;

/**
 * Builder for model BatchRetrieveInventoryChangesResponse
 *
 * @see BatchRetrieveInventoryChangesResponse
 */
class BatchRetrieveInventoryChangesResponseBuilder
{
    /**
     * @var BatchRetrieveInventoryChangesResponse
     */
    private $instance;

    private function __construct(BatchRetrieveInventoryChangesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Retrieve Inventory Changes Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchRetrieveInventoryChangesResponse());
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
     * Sets changes field.
     *
     * @param InventoryChange[]|null $value
     */
    public function changes(?array $value): self
    {
        $this->instance->setChanges($value);
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
     * Initializes a new Batch Retrieve Inventory Changes Response object.
     */
    public function build(): BatchRetrieveInventoryChangesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
