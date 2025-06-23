<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

class TenderSquareAccountDetailsStatus
{
    /**
     * The EDD\Vendor\Square Account payment has been authorized but not yet captured.
     */
    public const AUTHORIZED = 'AUTHORIZED';

    /**
     * The EDD\Vendor\Square Account payment was authorized and subsequently captured (i.e., completed).
     */
    public const CAPTURED = 'CAPTURED';

    /**
     * The EDD\Vendor\Square Account payment was authorized and subsequently voided (i.e., canceled).
     */
    public const VOIDED = 'VOIDED';

    /**
     * The EDD\Vendor\Square Account payment failed.
     */
    public const FAILED = 'FAILED';
}
