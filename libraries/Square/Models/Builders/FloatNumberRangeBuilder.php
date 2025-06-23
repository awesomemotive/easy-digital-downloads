<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\FloatNumberRange;

/**
 * Builder for model FloatNumberRange
 *
 * @see FloatNumberRange
 */
class FloatNumberRangeBuilder
{
    /**
     * @var FloatNumberRange
     */
    private $instance;

    private function __construct(FloatNumberRange $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Float Number Range Builder object.
     */
    public static function init(): self
    {
        return new self(new FloatNumberRange());
    }

    /**
     * Sets start at field.
     *
     * @param string|null $value
     */
    public function startAt(?string $value): self
    {
        $this->instance->setStartAt($value);
        return $this;
    }

    /**
     * Unsets start at field.
     */
    public function unsetStartAt(): self
    {
        $this->instance->unsetStartAt();
        return $this;
    }

    /**
     * Sets end at field.
     *
     * @param string|null $value
     */
    public function endAt(?string $value): self
    {
        $this->instance->setEndAt($value);
        return $this;
    }

    /**
     * Unsets end at field.
     */
    public function unsetEndAt(): self
    {
        $this->instance->unsetEndAt();
        return $this;
    }

    /**
     * Initializes a new Float Number Range object.
     */
    public function build(): FloatNumberRange
    {
        return CoreHelper::clone($this->instance);
    }
}
