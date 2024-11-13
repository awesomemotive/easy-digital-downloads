<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe;

/**
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $period_end The end of the invoicing period. This TDS applies to EDD\Vendor\Stripe fees collected during this invoicing period.
 * @property int $period_start The start of the invoicing period. This TDS applies to EDD\Vendor\Stripe fees collected during this invoicing period.
 * @property string $tax_deduction_account_number The TAN that was supplied to EDD\Vendor\Stripe when TDS was assessed
 */
class TaxDeductedAtSource extends ApiResource
{
    const OBJECT_NAME = 'tax_deducted_at_source';
}
