<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQuery;
use EDD\Vendor\Square\Models\CatalogQueryExact;
use EDD\Vendor\Square\Models\CatalogQueryItemsForItemOptions;
use EDD\Vendor\Square\Models\CatalogQueryItemsForModifierList;
use EDD\Vendor\Square\Models\CatalogQueryItemsForTax;
use EDD\Vendor\Square\Models\CatalogQueryItemVariationsForItemOptionValues;
use EDD\Vendor\Square\Models\CatalogQueryPrefix;
use EDD\Vendor\Square\Models\CatalogQueryRange;
use EDD\Vendor\Square\Models\CatalogQuerySet;
use EDD\Vendor\Square\Models\CatalogQuerySortedAttribute;
use EDD\Vendor\Square\Models\CatalogQueryText;

/**
 * Builder for model CatalogQuery
 *
 * @see CatalogQuery
 */
class CatalogQueryBuilder
{
    /**
     * @var CatalogQuery
     */
    private $instance;

    private function __construct(CatalogQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogQuery());
    }

    /**
     * Sets sorted attribute query field.
     *
     * @param CatalogQuerySortedAttribute|null $value
     */
    public function sortedAttributeQuery(?CatalogQuerySortedAttribute $value): self
    {
        $this->instance->setSortedAttributeQuery($value);
        return $this;
    }

    /**
     * Sets exact query field.
     *
     * @param CatalogQueryExact|null $value
     */
    public function exactQuery(?CatalogQueryExact $value): self
    {
        $this->instance->setExactQuery($value);
        return $this;
    }

    /**
     * Sets set query field.
     *
     * @param CatalogQuerySet|null $value
     */
    public function setQuery(?CatalogQuerySet $value): self
    {
        $this->instance->setSetQuery($value);
        return $this;
    }

    /**
     * Sets prefix query field.
     *
     * @param CatalogQueryPrefix|null $value
     */
    public function prefixQuery(?CatalogQueryPrefix $value): self
    {
        $this->instance->setPrefixQuery($value);
        return $this;
    }

    /**
     * Sets range query field.
     *
     * @param CatalogQueryRange|null $value
     */
    public function rangeQuery(?CatalogQueryRange $value): self
    {
        $this->instance->setRangeQuery($value);
        return $this;
    }

    /**
     * Sets text query field.
     *
     * @param CatalogQueryText|null $value
     */
    public function textQuery(?CatalogQueryText $value): self
    {
        $this->instance->setTextQuery($value);
        return $this;
    }

    /**
     * Sets items for tax query field.
     *
     * @param CatalogQueryItemsForTax|null $value
     */
    public function itemsForTaxQuery(?CatalogQueryItemsForTax $value): self
    {
        $this->instance->setItemsForTaxQuery($value);
        return $this;
    }

    /**
     * Sets items for modifier list query field.
     *
     * @param CatalogQueryItemsForModifierList|null $value
     */
    public function itemsForModifierListQuery(?CatalogQueryItemsForModifierList $value): self
    {
        $this->instance->setItemsForModifierListQuery($value);
        return $this;
    }

    /**
     * Sets items for item options query field.
     *
     * @param CatalogQueryItemsForItemOptions|null $value
     */
    public function itemsForItemOptionsQuery(?CatalogQueryItemsForItemOptions $value): self
    {
        $this->instance->setItemsForItemOptionsQuery($value);
        return $this;
    }

    /**
     * Sets item variations for item option values query field.
     *
     * @param CatalogQueryItemVariationsForItemOptionValues|null $value
     */
    public function itemVariationsForItemOptionValuesQuery(?CatalogQueryItemVariationsForItemOptionValues $value): self
    {
        $this->instance->setItemVariationsForItemOptionValuesQuery($value);
        return $this;
    }

    /**
     * Initializes a new Catalog Query object.
     */
    public function build(): CatalogQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
