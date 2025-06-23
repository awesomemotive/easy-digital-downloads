<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Specifies the sort key for events returned from a search.
 */
class SearchEventsSortField
{
    /**
     * Use the default sort key. The default behavior is to sort events by when they were created
     * (`created_at`).
     */
    public const DEFAULT_ = 'DEFAULT';
}
