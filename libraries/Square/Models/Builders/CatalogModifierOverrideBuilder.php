<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogModifierOverride;

/**
 * Builder for model CatalogModifierOverride
 *
 * @see CatalogModifierOverride
 */
class CatalogModifierOverrideBuilder
{
    /**
     * @var CatalogModifierOverride
     */
    private $instance;

    private function __construct(CatalogModifierOverride $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Modifier Override Builder object.
     *
     * @param string $modifierId
     */
    public static function init(string $modifierId): self
    {
        return new self(new CatalogModifierOverride($modifierId));
    }

    /**
     * Sets on by default field.
     *
     * @param bool|null $value
     */
    public function onByDefault(?bool $value): self
    {
        $this->instance->setOnByDefault($value);
        return $this;
    }

    /**
     * Unsets on by default field.
     */
    public function unsetOnByDefault(): self
    {
        $this->instance->unsetOnByDefault();
        return $this;
    }

    /**
     * Initializes a new Catalog Modifier Override object.
     */
    public function build(): CatalogModifierOverride
    {
        return CoreHelper::clone($this->instance);
    }
}
