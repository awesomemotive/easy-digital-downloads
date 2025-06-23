<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * The transaction type used in the disputed payment.
 */
class TransactionType
{
    public const DEBIT = 'DEBIT';

    public const CREDIT = 'CREDIT';
}
