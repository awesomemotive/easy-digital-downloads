<?php
/**
 * The Square Customer Deleted event.
 *
 * @package EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Customer;

/**
 * The Square Customer Deleted event.
 */
class CustomerDeleted extends Event {

	/**
	 * Process the event.
	 *
	 * @since 3.4.0
	 */
	public function process() {
		edd_debug_log( sprintf( 'Square webhook - Customer Deleted: %s', $this->data['id'] ) );
		// The Square customer object may have a reference ID, so we'll check for that.
		$reference_id = $this->data['reference_id'] ?? $this->object['customer']['reference_id'] ?? false;

		if ( false !== $reference_id ) {
			// See if we have an EDD customer with the same reference ID, and if so, delete the customer.
			$edd_customer = edd_get_customer( $reference_id );

			if ( false !== $edd_customer ) {
				// Delete the customer meta for the Square customer ID.
				edd_delete_customer_meta( $edd_customer->id, Customer::get_customer_id_meta_key() );
			}
		}
	}
}
