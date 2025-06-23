<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\InventoryAdjustmentGroup;

/**
 * Builder for model InventoryAdjustmentGroup
 *
 * @see InventoryAdjustmentGroup
 */
class InventoryAdjustmentGroupBuilder
{
    /**
     * @var InventoryAdjustmentGroup
     */
    private $instance;

    private function __construct(InventoryAdjustmentGroup $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Inventory Adjustment Group Builder object.
     */
    public static function init(): self
    {
        return new self(new InventoryAdjustmentGroup());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets root adjustment id field.
     *
     * @param string|null $value
     */
    public function rootAdjustmentId(?string $value): self
    {
        $this->instance->setRootAdjustmentId($value);
        return $this;
    }

    /**
     * Sets from state field.
     *
     * @param string|null $value
     */
    public function fromState(?string $value): self
    {
        $this->instance->setFromState($value);
        return $this;
    }

    /**
     * Sets to state field.
     *
     * @param string|null $value
     */
    public function toState(?string $value): self
    {
        $this->instance->setToState($value);
        return $this;
    }

    /**
     * Initializes a new Inventory Adjustment Group object.
     */
    public function build(): InventoryAdjustmentGroup
    {
        return CoreHelper::clone($this->instance);
    }
}
