<?php
/**
 * The Square Order Updated event.
 *
 * @package     EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Api;

/**
 * The Square Order Updated event.
 */
class OrderUpdated extends Event {

	/**
	 * Process the event.
	 *
	 * @since 3.4.0
	 * @throws \Exception If the order cannot be retrieved or the order does not have a Square Order ID.
	 */
	public function process() {
		edd_debug_log( sprintf( 'Square webhook - Order Updated: %s', $this->data['id'] ) );

		// Get the order object from Square so we can pull the meta data.
		$order = Api::client()->getOrdersApi()->retrieveOrder( $this->data['id'] );

		if ( ! $order->isSuccess() ) {
			edd_debug_log( sprintf( 'Square webhook - Error retrieving order: %s', $order->getErrors()[0]->getDetail() ) );
			throw new \Exception( $order->getErrors()[0]->getDetail() );
		}

		$order = $order->getResult()->getOrder();

		// Look for the Square Order ID in the order meta.
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT edd_order_id FROM $wpdb->edd_ordermeta WHERE meta_key = 'square_order_id' AND meta_value = %s", $this->data['id'] ) );

		if ( ! $order_id ) {
			edd_debug_log( sprintf( 'Square webhook - Order %s does not have a Square Order ID', $this->data['id'] ) );
			throw new \Exception( sprintf( 'Square webhook - Order %s does not have a Square Order ID', $this->data['id'] ) );
		}

		$order = edd_get_order( $order_id );

		// There are a few order state changes we should handle.
		switch ( $this->object['order_updated']['state'] ) {
			case 'COMPLETED':
				// The order was completed, so we need to update the order status if it needs it.
				if ( ! in_array( $order->status, edd_get_complete_order_statuses(), true ) ) {
					edd_update_order_status( $order_id, 'complete' );
				}
				break;

			case 'CANCELED':
				// The order was canceled, so we need to update the order status.
				edd_update_order_status( $order_id, 'failed' );
				break;

			case 'DRAFT':
				// The order was marked as a draft, so we need to update the order status if it needs it.
				if ( ! in_array( $order->status, edd_get_complete_order_statuses(), true ) ) {
					edd_update_order_status( $order_id, 'pending' );
				}
				break;
		}
	}
}
