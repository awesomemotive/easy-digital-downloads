<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Card;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListCardsResponse;

/**
 * Builder for model ListCardsResponse
 *
 * @see ListCardsResponse
 */
class ListCardsResponseBuilder
{
    /**
     * @var ListCardsResponse
     */
    private $instance;

    private function __construct(ListCardsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Cards Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListCardsResponse());
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
     * Sets cards field.
     *
     * @param Card[]|null $value
     */
    public function cards(?array $value): self
    {
        $this->instance->setCards($value);
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
     * Initializes a new List Cards Response object.
     */
    public function build(): ListCardsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
