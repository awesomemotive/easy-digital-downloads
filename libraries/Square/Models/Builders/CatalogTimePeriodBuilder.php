<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogTimePeriod;

/**
 * Builder for model CatalogTimePeriod
 *
 * @see CatalogTimePeriod
 */
class CatalogTimePeriodBuilder
{
    /**
     * @var CatalogTimePeriod
     */
    private $instance;

    private function __construct(CatalogTimePeriod $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Time Period Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogTimePeriod());
    }

    /**
     * Sets event field.
     *
     * @param string|null $value
     */
    public function event(?string $value): self
    {
        $this->instance->setEvent($value);
        return $this;
    }

    /**
     * Unsets event field.
     */
    public function unsetEvent(): self
    {
        $this->instance->unsetEvent();
        return $this;
    }

    /**
     * Initializes a new Catalog Time Period object.
     */
    public function build(): CatalogTimePeriod
    {
        return CoreHelper::clone($this->instance);
    }
}
