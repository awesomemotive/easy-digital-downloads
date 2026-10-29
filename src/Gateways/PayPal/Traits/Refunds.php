<?php
/**
 * Refunds Trait
 *
 * Records refunds issued in PayPal against EDD orders.
 *
 * @package    EDD\Gateways\PayPal\Traits
 * @copyright  Copyright (c) 2026, Sandhills Development, LLC
 * @license    https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.7.3
 */

namespace EDD\Gateways\PayPal\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Refunds Trait
 *
 * Records refunds issued in PayPal against EDD orders.
 *
 * @since 3.7.3
 */
trait Refunds {

	/**
	 * Records a PayPal refund as a full or partial refund of the order.
	 *
	 * A refund of at least the order total is a full refund.
	 *
	 * @since 3.7.3
	 *
	 * @param \EDD\Orders\Order $order                 The order being refunded.
	 * @param float             $refunded_amount       The positive amount refunded in PayPal.
	 * @param string            $currency              The refund currency.
	 * @param string            $refund_transaction_id The PayPal refund transaction ID.
	 * @param array             $note_prefixes         {
	 *     Translated order note prefixes.
	 *
	 *     @type string $full    Prefix for a full refund note.
	 *     @type string $partial Prefix for a partial refund note.
	 * }
	 * @return void
	 */
	private function process_refund( $order, $refunded_amount, $currency, $refund_transaction_id, $note_prefixes ) {
		$payment_note = sprintf(
			/* translators: 1: Amount refunded, 2: Original payment transaction ID, 3: Refund transaction ID */
			esc_html__( 'Amount: %1$s; Payment transaction ID: %2$s; Refund transaction ID: %3$s', 'easy-digital-downloads' ),
			edd_currency_filter( edd_format_amount( $refunded_amount ), $currency ),
			esc_html( $order->get_transaction_id() ),
			esc_html( $refund_transaction_id )
		);

		// Order totals can carry sub-cent precision, but PayPal only refunds whole currency units.
		$decimals    = edd_currency_decimal_filter( 2, $order->currency );
		$order_total = round( (float) $order->total, $decimals );

		if ( round( (float) $refunded_amount, $decimals ) < $order_total ) {
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => $note_prefixes['partial'] . ' ' . $payment_note,
				)
			);
			edd_update_order_status( $order->id, 'partially_refunded' );

			return;
		}

		edd_add_note(
			array(
				'object_type' => 'order',
				'object_id'   => $order->id,
				'content'     => $note_prefixes['full'] . ' ' . $payment_note,
			)
		);
		add_filter( 'edd_is_order_refundable_by_override', '__return_true' );
		$refund_id = edd_refund_order( $order->id );
		remove_filter( 'edd_is_order_refundable_by_override', '__return_true' );

		if ( is_wp_error( $refund_id ) ) {
			edd_update_order_status( $order->id, 'refunded' );

			return;
		}

		edd_add_order_transaction(
			array(
				'object_type'    => 'order',
				'object_id'      => $refund_id,
				'transaction_id' => $refund_transaction_id,
				'gateway'        => 'paypal_commerce',
				'total'          => edd_negate_amount( $refunded_amount ),
				'status'         => 'complete',
				'currency'       => $currency,
			)
		);
	}
}
