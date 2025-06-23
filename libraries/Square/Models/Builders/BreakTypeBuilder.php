<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BreakType;

/**
 * Builder for model BreakType
 *
 * @see BreakType
 */
class BreakTypeBuilder
{
    /**
     * @var BreakType
     */
    private $instance;

    private function __construct(BreakType $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Break Type Builder object.
     *
     * @param string $locationId
     * @param string $breakName
     * @param string $expectedDuration
     * @param bool $isPaid
     */
    public static function init(string $locationId, string $breakName, string $expectedDuration, bool $isPaid): self
    {
        return new self(new BreakType($locationId, $breakName, $expectedDuration, $isPaid));
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
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
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
     * Initializes a new Break Type object.
     */
    public function build(): BreakType
    {
        return CoreHelper::clone($this->instance);
    }
}
