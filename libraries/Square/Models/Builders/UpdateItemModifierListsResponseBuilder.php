<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\UpdateItemModifierListsResponse;

/**
 * Builder for model UpdateItemModifierListsResponse
 *
 * @see UpdateItemModifierListsResponse
 */
class UpdateItemModifierListsResponseBuilder
{
    /**
     * @var UpdateItemModifierListsResponse
     */
    private $instance;

    private function __construct(UpdateItemModifierListsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Update Item Modifier Lists Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpdateItemModifierListsResponse());
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
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Update Item Modifier Lists Response object.
     */
    public function build(): UpdateItemModifierListsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
