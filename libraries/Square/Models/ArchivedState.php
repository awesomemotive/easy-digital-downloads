<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Defines the values for the `archived_state` query expression
 * used in [SearchCatalogItems]($e/Catalog/SearchCatalogItems)
 * to return the archived, not archived or either type of catalog items.
 */
class ArchivedState
{
    /**
     * Requested items are not archived with the `is_archived` attribute set to `false`.
     */
    public const ARCHIVED_STATE_NOT_ARCHIVED = 'ARCHIVED_STATE_NOT_ARCHIVED';

    /**
     * Requested items are archived with the `is_archived` attribute set to `true`.
     */
    public const ARCHIVED_STATE_ARCHIVED = 'ARCHIVED_STATE_ARCHIVED';

    /**
     * Requested items can be archived or not archived.
     */
    public const ARCHIVED_STATE_ALL = 'ARCHIVED_STATE_ALL';
}
