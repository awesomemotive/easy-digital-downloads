<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogObjectCategory;

/**
 * Builder for model CatalogObjectCategory
 *
 * @see CatalogObjectCategory
 */
class CatalogObjectCategoryBuilder
{
    /**
     * @var CatalogObjectCategory
     */
    private $instance;

    private function __construct(CatalogObjectCategory $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Object Category Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogObjectCategory());
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
     * Sets ordinal field.
     *
     * @param int|null $value
     */
    public function ordinal(?int $value): self
    {
        $this->instance->setOrdinal($value);
        return $this;
    }

    /**
     * Unsets ordinal field.
     */
    public function unsetOrdinal(): self
    {
        $this->instance->unsetOrdinal();
        return $this;
    }

    /**
     * Initializes a new Catalog Object Category object.
     */
    public function build(): CatalogObjectCategory
    {
        return CoreHelper::clone($this->instance);
    }
}
