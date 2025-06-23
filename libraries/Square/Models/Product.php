<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Indicates the EDD\Vendor\Square product used to generate a change.
 */
class Product
{
    /**
     * EDD\Vendor\Square Point of Sale application.
     */
    public const SQUARE_POS = 'SQUARE_POS';

    /**
     * EDD\Vendor\Square Connect APIs (for example, Orders API or Checkout API).
     */
    public const EXTERNAL_API = 'EXTERNAL_API';

    /**
     * A EDD\Vendor\Square subscription (various products).
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
     * EDD\Vendor\Square Dashboard.
     */
    public const DASHBOARD = 'DASHBOARD';

    /**
     * Item Library Import.
     */
    public const ITEM_LIBRARY_IMPORT = 'ITEM_LIBRARY_IMPORT';

    /**
     * A EDD\Vendor\Square product that does not match any other value.
     */
    public const OTHER = 'OTHER';
}
