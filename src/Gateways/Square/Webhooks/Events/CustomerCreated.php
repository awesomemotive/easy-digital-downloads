<?php
/**
 * The Square Customer Created event.
 *
 * @package     EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Customer;

/**
 * The Square Customer Created event.
 */
class CustomerCreated extends Event {

	/**
	 * Process the event.
	 *
	 * @since 3.4.0
	 */
	public function process() {
		edd_debug_log( sprintf( 'Square webhook - Customer Created: %s', $this->data['id'] ) );
		// See if we have an EDD customer with the same email address.
		$customer = edd_get_customer_by( 'email', $this->object['customer']['email_address'] );

		if ( false !== $customer ) {
			// Check to see if the customer has a Square ID, and if not, add it.
			if ( ! Customer::has_square_customer_id( $customer->id ) ) {
				edd_update_customer_meta( $customer->id, Customer::get_customer_id_meta_key(), $this->data['id'] );
			}
		}
	}
}
