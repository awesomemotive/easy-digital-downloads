<?php
/**
 * The Square Refund Updated event.
 *
 * @package     EDD\Gateways\Square\Webhooks\Events
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Webhooks\Events;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The Square Refund Updated event.
 */
class RefundUpdated extends Event {

	/**
	 * Process the event.
	 *
	 * @since 3.4.0
	 * @throws \Exception If the refund does not have an EDD order.
	 */
	public function process() {
		edd_debug_log( sprintf( 'Square webhook - Refund Updated: %s', $this->data['id'] ) );

		// Check the state of the refund, and if it's not 'COMPLETED', don't do anything.
		if ( 'COMPLETED' !== $this->object['refund']['state'] ) {
			edd_debug_log( sprintf( 'Square webhook - Refund %s is not completed, skipping', $this->data['id'] ) );
			return;
		}

		// Get the order transaction.
		$order_id = edd_get_order_id_from_transaction_id( $this->object['refund']['payment_id'] );
		if ( ! $order_id ) {
			edd_debug_log( sprintf( 'Square webhook - Refund %s does not have an EDD order', $this->data['id'] ) );
			throw new \Exception( sprintf( 'Square webhook - Refund %s does not have an EDD order', $this->data['id'] ) );
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			edd_debug_log( sprintf( 'Square webhook - Refund %s does not have an EDD order', $this->data['id'] ) );
			throw new \Exception( sprintf( 'Square webhook - Refund %s does not have an EDD order', $this->data['id'] ) );
		}

		// If this was completely refunded, set the status to refunded.
		$order_total = $order->total;
		// Square refunds are in cents, so we need to convert our order total to cents.
		$order_total = $order_total * 100;

		if ( $this->object['refund']['amount_money']['amount'] === $order_total && edd_is_order_refundable( $order->id ) ) {
			add_filter( 'edd_is_order_refundable_by_override', '__return_true' );
			$refund_id = edd_refund_order( $order->id );
			remove_filter( 'edd_is_order_refundable_by_override', '__return_true' );

			if ( $refund_id && ! is_wp_error( $refund_id ) ) {
				edd_add_order_transaction(
					array(
						'object_type'    => 'order',
						'object_id'      => $refund_id,
						'transaction_id' => $this->object['refund']['id'],
						'gateway'        => 'square',
						'total'          => $order->total,
						'status'         => 'complete',
						'currency'       => $order->currency,
					)
				);
			} else {
				edd_update_order_status( $order->id, 'refunded' );
			}
			/* translators: The charge ID from Square that is being refunded. */
			$note = sprintf( __( 'Charge %s has been fully refunded in Square.', 'easy-digital-downloads' ), $this->object['refund']['id'] );
		} else {
			edd_update_order_status( $order->id, 'partially_refunded' );
			/* translators: The charge ID from Square that is being partially refunded. */
			$note = sprintf( __( 'Charge %s partially refunded in Square.', 'easy-digital-downloads' ), $this->object->id );
		}
		edd_add_note(
			array(
				'object_id'   => $order->id,
				'object_type' => 'order',
				'content'     => $note,
			)
		);
	}
}
