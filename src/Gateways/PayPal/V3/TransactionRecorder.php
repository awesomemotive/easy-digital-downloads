<?php
/**
 * PayPal V3 capture transaction recording.
 *
 * Records the EDD order transaction and the "PayPal Transaction ID" note for a
 * captured PayPal payment. Every v3 capture path (smart-button checkout,
 * Fastlane, and the on-checkout card fields) recorded this identically;
 * centralizing it keeps the transaction row and note consistent across them.
 *
 * @package     EDD\Gateways\PayPal\V3
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.9
 */

namespace EDD\Gateways\PayPal\V3;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * TransactionRecorder class.
 *
 * @since 3.6.9
 */
class TransactionRecorder {

	/**
	 * Records the capture transaction and transaction-ID note on an order.
	 *
	 * No-ops when the transaction id is empty. The id is sanitized here so
	 * callers may pass it straight from the PayPal response.
	 *
	 * @since 3.6.9
	 *
	 * @param int    $order_id       EDD order ID.
	 * @param string $transaction_id PayPal capture ID.
	 * @return void
	 */
	public static function record( $order_id, $transaction_id ) {
		$transaction_id = sanitize_text_field( (string) $transaction_id );
		if ( '' === $transaction_id ) {
			return;
		}

		$order = edd_get_order( $order_id );

		edd_add_order_transaction(
			array(
				'object_id'      => $order_id,
				'object_type'    => 'order',
				'transaction_id' => $transaction_id,
				'gateway'        => 'paypal_commerce',
				'status'         => 'complete',
				'total'          => $order ? $order->total : 0,
			)
		);

		edd_add_note(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'content'     => sprintf(
					/* translators: %s: PayPal Transaction ID */
					__( 'PayPal Transaction ID: %s', 'easy-digital-downloads' ),
					esc_html( $transaction_id )
				),
			)
		);
	}

	/**
	 * Notes a declined capture on an order without recording it as a transaction.
	 *
	 * The order's first transaction is the capture that webhooks and refunds act on, so a declined
	 * capture must never become it.
	 *
	 * @since 3.7.2
	 *
	 * @param int    $order_id       EDD order ID.
	 * @param string $transaction_id PayPal capture ID.
	 * @return void
	 */
	public static function record_declined( $order_id, $transaction_id ) {
		$transaction_id = sanitize_text_field( (string) $transaction_id );
		if ( '' === $transaction_id ) {
			return;
		}

		edd_add_note(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'content'     => sprintf(
					/* translators: %s: PayPal capture ID */
					__( 'PayPal declined capture %s.', 'easy-digital-downloads' ),
					esc_html( $transaction_id )
				),
			)
		);
	}
}
