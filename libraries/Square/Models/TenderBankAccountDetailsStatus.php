<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates the bank account payment's current status.
 */
class TenderBankAccountDetailsStatus
{
    /**
     * The bank account payment is in progress.
     */
    public const PENDING = 'PENDING';

    /**
     * The bank account payment has been completed.
     */
    public const COMPLETED = 'COMPLETED';

    /**
     * The bank account payment failed.
     */
    public const FAILED = 'FAILED';
}
