<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQueryItemsForTax;

/**
 * Builder for model CatalogQueryItemsForTax
 *
 * @see CatalogQueryItemsForTax
 */
class CatalogQueryItemsForTaxBuilder
{
    /**
     * @var CatalogQueryItemsForTax
     */
    private $instance;

    private function __construct(CatalogQueryItemsForTax $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Items For Tax Builder object.
     *
     * @param string[] $taxIds
     */
    public static function init(array $taxIds): self
    {
        return new self(new CatalogQueryItemsForTax($taxIds));
    }

    /**
     * Initializes a new Catalog Query Items For Tax object.
     */
    public function build(): CatalogQueryItemsForTax
    {
        return CoreHelper::clone($this->instance);
    }
}
