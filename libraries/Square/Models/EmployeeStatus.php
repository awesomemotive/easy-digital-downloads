<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * The status of the Employee being retrieved.
 *
 * DEPRECATED at version 2020-08-26. Replaced by [TeamMemberStatus](entity:TeamMemberStatus).
 */
class EmployeeStatus
{
    /**
     * Specifies that the employee is in the Active state.
     */
    public const ACTIVE = 'ACTIVE';

    /**
     * Specifies that the employee is in the Inactive state.
     */
    public const INACTIVE = 'INACTIVE';
}
