<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\InventoryCount;
use EDD\Vendor\Square\Models\RetrieveInventoryCountResponse;

/**
 * Builder for model RetrieveInventoryCountResponse
 *
 * @see RetrieveInventoryCountResponse
 */
class RetrieveInventoryCountResponseBuilder
{
    /**
     * @var RetrieveInventoryCountResponse
     */
    private $instance;

    private function __construct(RetrieveInventoryCountResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Inventory Count Response Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveInventoryCountResponse());
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
     * Initializes a new Retrieve Inventory Count Response object.
     */
    public function build(): RetrieveInventoryCountResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
