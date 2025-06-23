<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogQueryText;

/**
 * Builder for model CatalogQueryText
 *
 * @see CatalogQueryText
 */
class CatalogQueryTextBuilder
{
    /**
     * @var CatalogQueryText
     */
    private $instance;

    private function __construct(CatalogQueryText $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Query Text Builder object.
     *
     * @param string[] $keywords
     */
    public static function init(array $keywords): self
    {
        return new self(new CatalogQueryText($keywords));
    }

    /**
     * Initializes a new Catalog Query Text object.
     */
    public function build(): CatalogQueryText
    {
        return CoreHelper::clone($this->instance);
    }
}
