<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

class TenderBuyNowPayLaterDetailsStatus
{
    /**
     * The buy now pay later payment has been authorized but not yet captured.
     */
    public const AUTHORIZED = 'AUTHORIZED';

    /**
     * The buy now pay later payment was authorized and subsequently captured (i.e., completed).
     */
    public const CAPTURED = 'CAPTURED';

    /**
     * The buy now pay later payment was authorized and subsequently voided (i.e., canceled).
     */
    public const VOIDED = 'VOIDED';

    /**
     * The buy now pay later payment failed.
     */
    public const FAILED = 'FAILED';
}
