<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogAvailabilityPeriod;

/**
 * Builder for model CatalogAvailabilityPeriod
 *
 * @see CatalogAvailabilityPeriod
 */
class CatalogAvailabilityPeriodBuilder
{
    /**
     * @var CatalogAvailabilityPeriod
     */
    private $instance;

    private function __construct(CatalogAvailabilityPeriod $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Availability Period Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogAvailabilityPeriod());
    }

    /**
     * Sets start local time field.
     *
     * @param string|null $value
     */
    public function startLocalTime(?string $value): self
    {
        $this->instance->setStartLocalTime($value);
        return $this;
    }

    /**
     * Unsets start local time field.
     */
    public function unsetStartLocalTime(): self
    {
        $this->instance->unsetStartLocalTime();
        return $this;
    }

    /**
     * Sets end local time field.
     *
     * @param string|null $value
     */
    public function endLocalTime(?string $value): self
    {
        $this->instance->setEndLocalTime($value);
        return $this;
    }

    /**
     * Unsets end local time field.
     */
    public function unsetEndLocalTime(): self
    {
        $this->instance->unsetEndLocalTime();
        return $this;
    }

    /**
     * Sets day of week field.
     *
     * @param string|null $value
     */
    public function dayOfWeek(?string $value): self
    {
        $this->instance->setDayOfWeek($value);
        return $this;
    }

    /**
     * Initializes a new Catalog Availability Period object.
     */
    public function build(): CatalogAvailabilityPeriod
    {
        return CoreHelper::clone($this->instance);
    }
}
