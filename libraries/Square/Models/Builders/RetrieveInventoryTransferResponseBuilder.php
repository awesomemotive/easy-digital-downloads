<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\InventoryTransfer;
use EDD\Vendor\Square\Models\RetrieveInventoryTransferResponse;

/**
 * Builder for model RetrieveInventoryTransferResponse
 *
 * @see RetrieveInventoryTransferResponse
 */
class RetrieveInventoryTransferResponseBuilder
{
    /**
     * @var RetrieveInventoryTransferResponse
     */
    private $instance;

    private function __construct(RetrieveInventoryTransferResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Inventory Transfer Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveInventoryTransferResponse());
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
     * Sets transfer field.
     *
     * @param InventoryTransfer|null $value
     */
    public function transfer(?InventoryTransfer $value): self
    {
        $this->instance->setTransfer($value);
        return $this;
    }

    /**
     * Initializes a new Retrieve Inventory Transfer Response object.
     */
    public function build(): RetrieveInventoryTransferResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
