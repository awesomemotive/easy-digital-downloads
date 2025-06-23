<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BatchChangeInventoryResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\InventoryChange;
use EDD\Vendor\Square\Models\InventoryCount;

/**
 * Builder for model BatchChangeInventoryResponse
 *
 * @see BatchChangeInventoryResponse
 */
class BatchChangeInventoryResponseBuilder
{
    /**
     * @var BatchChangeInventoryResponse
     */
    private $instance;

    private function __construct(BatchChangeInventoryResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Batch Change Inventory Response Builder object.
     */
    public static function init(): self
    {
        return new self(new BatchChangeInventoryResponse());
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
     * Sets counts field.
     *
     * @param InventoryCount[]|null $value
     */
    public function counts(?array $value): self
    {
        $this->instance->setCounts($value);
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
     * Initializes a new Batch Change Inventory Response object.
     */
    public function build(): BatchChangeInventoryResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
