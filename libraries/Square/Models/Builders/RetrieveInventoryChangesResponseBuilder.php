<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\InventoryChange;
use EDD\Vendor\Square\Models\RetrieveInventoryChangesResponse;

/**
 * Builder for model RetrieveInventoryChangesResponse
 *
 * @see RetrieveInventoryChangesResponse
 */
class RetrieveInventoryChangesResponseBuilder
{
    /**
     * @var RetrieveInventoryChangesResponse
     */
    private $instance;

    private function __construct(RetrieveInventoryChangesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Inventory Changes Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveInventoryChangesResponse());
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
     * Initializes a new Retrieve Inventory Changes Response object.
     */
    public function build(): RetrieveInventoryChangesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
