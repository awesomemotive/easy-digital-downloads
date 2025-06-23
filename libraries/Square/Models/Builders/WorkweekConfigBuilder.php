<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\WorkweekConfig;

/**
 * Builder for model WorkweekConfig
 *
 * @see WorkweekConfig
 */
class WorkweekConfigBuilder
{
    /**
     * @var WorkweekConfig
     */
    private $instance;

    private function __construct(WorkweekConfig $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Workweek Config Builder object.
     *
     * @param string $startOfWeek
     * @param string $startOfDayLocalTime
     */
    public static function init(string $startOfWeek, string $startOfDayLocalTime): self
    {
        return new self(new WorkweekConfig($startOfWeek, $startOfDayLocalTime));
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
     * Initializes a new Workweek Config object.
     */
    public function build(): WorkweekConfig
    {
        return CoreHelper::clone($this->instance);
    }
}
