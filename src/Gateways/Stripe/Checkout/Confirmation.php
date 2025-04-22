<?php
/**
 * Handle payments that require confirmation for the Stripe Gateway.
 *
 * @package EDD\Gateways\Stripe\Checkout
 * @since 3.3.5
 */

namespace EDD\Gateways\Stripe\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Gateways\Stripe\Intents;

/**
 * Confirmation class.
 */
class Confirmation {

	/**
	 * Validates the payment intent.
	 *
	 * @param string $intent_id The payment intent to validate.
	 * @return void
	 */
	public static function validate( $intent_id ) {
		$order = self::get_order( $intent_id );
		if ( ! $order ) {
			edd_redirect( edd_get_failed_transaction_uri() );
		}

		$purchase_session = edd_get_purchase_session();
		if ( 'complete' === $order->status && $purchase_session ) {
			edd_redirect( edd_get_success_page_uri() );
		}

		$args = array(
			'payment-confirmation' => 'stripe',
		);

		// This tells the confirmation page to show the processing template.
		if ( $purchase_session && 'pending' === $order->status ) {
			$args['status'] = 'processing';
		}

		edd_redirect(
			add_query_arg(
				$args,
				edd_get_success_page_uri()
			)
		);
	}

	/**
	 * Get the order from the PaymentIntent.
	 *
	 * @since 3.3.5
	 * @param string $intent_id PaymentIntent ID.
	 * @return \EDD\Orders\Order|false
	 */
	private static function get_order( $intent_id ) {
		$intent = Intents::get( $intent_id );
		if ( ! $intent ) {
			edd_record_gateway_error(
				__( 'Stripe Gateway Error', 'easy-digital-downloads' ),
				sprintf(
					/* translators: %s: Payment intent ID */
					__( 'The payment intent could not be retrieved: %s', 'easy-digital-downloads' ),
					$intent_id
				)
			);
			return false;
		}

		// If the intent is canceled, return false.
		if ( in_array( $intent->status, array( 'canceled', 'requires_payment_method' ), true ) ) {
			edd_record_gateway_error(
				__( 'Stripe Gateway Error', 'easy-digital-downloads' ),
				sprintf(
					/* translators: %1$s: Payment intent status, %2$s: Payment intent ID */
					__( 'The payment intent status is %1$s for payment intent: %2$s', 'easy-digital-downloads' ),
					$intent->status,
					$intent_id
				)
			);
			return false;
		}

		// Get the order ID from the metadata (edd_payment_id).
		$metadata = $intent->metadata;
		if ( ! isset( $metadata->edd_payment_id ) ) {
			edd_record_gateway_error(
				__( 'Stripe Gateway Error', 'easy-digital-downloads' ),
				sprintf(
					/* translators: %s: Payment intent ID */
					__( 'The payment intent metadata does not contain an order ID: %s', 'easy-digital-downloads' ),
					$intent_id
				)
			);
			return false;
		}

		$order = edd_get_order( $metadata->edd_payment_id );
		if ( ! $order ) {
			edd_record_gateway_error(
				__( 'Stripe Gateway Error', 'easy-digital-downloads' ),
				sprintf(
					/* translators: %1$s: Order ID, %2$s: Payment intent ID */
					__( 'The order %1$s could not be retrieved from the payment intent metadata: %2$s', 'easy-digital-downloads' ),
					$metadata->edd_payment_id,
					$intent_id
				),
				$metadata->edd_payment_id
			);
			return false;
		}

		// If the intent is complete but the order is not, mark it complete.
		if ( 'succeeded' === $intent->status && 'complete' !== $order->status ) {
			Complete::mark_complete( $order, $intent );

			return edd_get_order( $order->id );
		}

		return $order;
	}
}
