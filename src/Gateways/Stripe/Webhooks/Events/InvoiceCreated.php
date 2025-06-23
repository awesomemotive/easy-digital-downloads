<?php
/**
 * Handle the Stripe Invoice Created event.
 *
 * @package     EDD\Gateways\Stripe\Webhooks\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Stripe\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Stripe\ApplicationFee;

/**
 * Class InvoiceCreated
 *
 * @since 3.4.0
 */
class InvoiceCreated extends Event {

	/**
	 * If a store qualifies for an application fee, set the application fee on the invoice.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function process() {
		// Only process draft invoices as we can only modify draft invoices.
		if ( 'draft' !== $this->object->status ) {
			return;
		}

		$application_fee = new ApplicationFee();

		// Check if we should collect an application fee.
		if ( ! $application_fee->has_application_fee() ) {
			return;
		}

		// Get the invoice amount to calculate the fee.
		$invoice_amount = $this->object->amount_due;

		// Only proceed if there's an amount due.
		if ( empty( $invoice_amount ) || $invoice_amount <= 0 ) {
			return;
		}

		// Calculate the application fee amount (in cents).
		$fee_amount = $application_fee->get_application_fee_amount( $invoice_amount );

		// Only add fee if it's greater than 0.
		if ( $fee_amount <= 0 ) {
			return;
		}

		try {
			edds_api_request(
				'Invoice',
				'update',
				$this->object->id,
				array(
					'application_fee_amount' => $fee_amount,
				)
			);

			edd_debug_log(
				sprintf(
					'Application fee set on invoice %s: %s',
					$this->object->id,
					$fee_amount
				)
			);

		} catch ( \Exception $e ) {
			edd_debug_log(
				sprintf(
					'Failed to set application fee on invoice %s: %s',
					$this->object->id,
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * The requirements are met if the object is not empty and has an ID.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function requirements_met() {
		return ! empty( $this->object ) && ! empty( $this->object->id );
	}
}
