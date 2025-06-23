<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RetrieveInventoryChangesRequest;

/**
 * Builder for model RetrieveInventoryChangesRequest
 *
 * @see RetrieveInventoryChangesRequest
 */
class RetrieveInventoryChangesRequestBuilder
{
    /**
     * @var RetrieveInventoryChangesRequest
     */
    private $instance;

    private function __construct(RetrieveInventoryChangesRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Retrieve Inventory Changes Request Builder object.
     */
    public static function init(): self
    {
        return new self(new RetrieveInventoryChangesRequest());
    }

    /**
     * Sets location ids field.
     *
     * @param string|null $value
     */
    public function locationIds(?string $value): self
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
     * Initializes a new Retrieve Inventory Changes Request object.
     */
    public function build(): RetrieveInventoryChangesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
