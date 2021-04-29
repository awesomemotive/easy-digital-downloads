<?php
/**
 * Webhook Event: PAYMENT.CAPTURE.REFUNDED
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks\Events
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\PayPal\Webhooks\Events;

class Payment_Capture_Refunded extends Webhook_Event {

	/**
	 * Processes the event.
	 *
	 * @throws \EDD\PayPal\Exceptions\API_Exception
	 * @throws \EDD\PayPal\Exceptions\Authentication_Exception
	 *
	 * @since 2.11
	 */
	protected function process_event() {
		$payment = $this->get_payment_from_resource_link();

		if ( 'refunded' === $payment->status ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as payment status is already refunded.' );

			wp_send_json_success( 'Payment already refunded.', 200 );
		}

		$payment_amount  = edd_get_payment_amount( $payment->ID );
		$refunded_amount = isset( $this->event->resource->amount->value ) ? $this->event->resource->amount->value : $payment_amount;
		$currency        = isset( $this->event->resource->amount->currency_code ) ? $this->event->resource->amount->currency_code : $payment->currency;

		/* Translators: %1$s - Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
		$payment_note = sprintf(
			esc_html__( 'Amount: %1$s; Payment transaction ID: %2$s; Refund transaction ID: %3$s', 'easy-digital-downloads' ),
			edd_currency_filter( edd_format_amount( $refunded_amount ), $currency ),
			esc_html( $payment->transaction_id ),
			esc_html( $this->event->resource->id )
		);

		// Partial refund.
		if ( (float) $refunded_amount < (float) $payment_amount ) {
			edd_insert_payment_note( $payment->ID, esc_html__( 'Partial refund processed in PayPal.', 'easy-digital-downloads' ) . ' ' . $payment_note );
		} else {
			// Full refund.
			edd_insert_payment_note( $payment->ID, esc_html__( 'Full refund processed in PayPal.', 'easy-digital-downloads' ) . ' ' . $payment_note );
			edd_update_payment_status( $payment->ID, 'refunded' );
		}
	}
}
