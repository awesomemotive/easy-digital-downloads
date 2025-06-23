<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates the EDD\Vendor\Square product used to process a transaction.
 */
class TransactionProduct
{
    /**
     * EDD\Vendor\Square Point of Sale.
     */
    public const REGISTER = 'REGISTER';

    /**
     * The EDD\Vendor\Square Connect API.
     */
    public const EXTERNAL_API = 'EXTERNAL_API';

    /**
     * A EDD\Vendor\Square subscription for one of multiple products.
     */
    public const BILLING = 'BILLING';

    /**
     * EDD\Vendor\Square Appointments.
     */
    public const APPOINTMENTS = 'APPOINTMENTS';

    /**
     * EDD\Vendor\Square Invoices.
     */
    public const INVOICES = 'INVOICES';

    /**
     * EDD\Vendor\Square Online Store.
     */
    public const ONLINE_STORE = 'ONLINE_STORE';

    /**
     * EDD\Vendor\Square Payroll.
     */
    public const PAYROLL = 'PAYROLL';

    /**
     * A EDD\Vendor\Square product that does not match any other value.
     */
    public const OTHER = 'OTHER';
}
