<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CreateLocationResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Location;

/**
 * Builder for model CreateLocationResponse
 *
 * @see CreateLocationResponse
 */
class CreateLocationResponseBuilder
{
    /**
     * @var CreateLocationResponse
     */
    private $instance;

    private function __construct(CreateLocationResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Location Response Builder object.
     */
    public static function init(): self
    {
        return new self(new CreateLocationResponse());
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
     * Sets location field.
     *
     * @param Location|null $value
     */
    public function location(?Location $value): self
    {
        $this->instance->setLocation($value);
        return $this;
    }

    /**
     * Initializes a new Create Location Response object.
     */
    public function build(): CreateLocationResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
