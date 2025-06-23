<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQueryRange;

/**
 * Builder for model CatalogQueryRange
 *
 * @see CatalogQueryRange
 */
class CatalogQueryRangeBuilder
{
    /**
     * @var CatalogQueryRange
     */
    private $instance;

    private function __construct(CatalogQueryRange $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Range Builder object.
     *
     * @param string $attributeName
     */
    public static function init(string $attributeName): self
    {
        return new self(new CatalogQueryRange($attributeName));
    }

    /**
     * Sets attribute min value field.
     *
     * @param int|null $value
     */
    public function attributeMinValue(?int $value): self
    {
        $this->instance->setAttributeMinValue($value);
        return $this;
    }

    /**
     * Unsets attribute min value field.
     */
    public function unsetAttributeMinValue(): self
    {
        $this->instance->unsetAttributeMinValue();
        return $this;
    }

    /**
     * Sets attribute max value field.
     *
     * @param int|null $value
     */
    public function attributeMaxValue(?int $value): self
    {
        $this->instance->setAttributeMaxValue($value);
        return $this;
    }

    /**
     * Unsets attribute max value field.
     */
    public function unsetAttributeMaxValue(): self
    {
        $this->instance->unsetAttributeMaxValue();
        return $this;
    }

    /**
     * Initializes a new Catalog Query Range object.
     */
    public function build(): CatalogQueryRange
    {
        return CoreHelper::clone($this->instance);
    }
}
