<?php
/**
 * A trait for order retrieval within Stripe Webhooks.
 *
 * @package EDD\Gateways\Stripe\Webhooks\Events\Traits
 * @since 3.3.5
 */

namespace EDD\Gateways\Stripe\Webhooks\Events\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

trait Order {

	/**
	 * Retrieve the order from the object.
	 *
	 * @since 3.3.5
	 * @param string|null $transaction_id The transaction ID to use to retrieve the order.
	 * @return \EDD\Orders\Order|false
	 */
	private function get_order( $transaction_id = null ) {
		if ( ! $transaction_id ) {
			$transaction_id = $this->object->id;
		}
		$order_id = edd_get_order_id_from_transaction_id( $transaction_id );
		if ( $order_id ) {
			return edd_get_order( $order_id );
		}

		// If the order transaction wasn't updated with the final transaction ID, update it now.
		$transaction = edd_get_order_transaction_by( 'transaction_id', $this->object->payment_intent );
		if ( $transaction ) {
			$order_id = $transaction->object_id;
			edd_update_order_transaction(
				$transaction->id,
				array(
					'transaction_id' => $this->object->id,
					'status'         => 'complete',
				)
			);

			return edd_get_order( $order_id );
		}

		return false;
	}

	/**
	 * Get a refund ID for an order.
	 *
	 * This method will either create a refund and return it's ID or return the ID of an existing refund.
	 *
	 * @param \EDD\Orders\Order $order The order object.
	 * @return int|bool The refund ID or false if no refund was created.
	 */
	private function get_refund_id( $order ) {
		if ( edd_is_order_refundable( $order->id ) ) {
			return edd_refund_order( $order->id );
		}

		$refunds = edd_get_orders(
			array(
				'type'   => 'refund',
				'parent' => $order->id,
			)
		);

		if ( ! empty( $refunds ) ) {
			return $refunds[0]->id;
		}

		return false;
	}
}
