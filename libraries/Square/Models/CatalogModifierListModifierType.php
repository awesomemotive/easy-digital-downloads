<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Defines the type of `CatalogModifierList`.
 */
class CatalogModifierListModifierType
{
    /**
     * The `CatalogModifierList` instance is a non-empty list of non text-based modifiers.
     */
    public const LIST_ = 'LIST';

    /**
     * The `CatalogModifierList` instance is a single text-based modifier.
     */
    public const TEXT = 'TEXT';
}
