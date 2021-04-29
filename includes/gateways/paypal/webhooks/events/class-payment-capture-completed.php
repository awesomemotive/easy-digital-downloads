<?php
/**
 * Webhook Event: PAYMENT.CAPTURE.COMPLETED
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal\Webhooks\Events
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\PayPal\Webhooks\Events;

class Payment_Capture_Completed extends Webhook_Event {

	/**
	 * Processes the event.
	 *
	 * @throws \EDD\PayPal\Exceptions\API_Exception
	 * @throws \EDD\PayPal\Exceptions\Authentication_Exception
	 * @throws \Exception
	 *
	 * @since 2.11
	 */
	protected function process_event() {
		$payment = $this->get_payment_from_resource_link();

		// Bail if the payment has already been completed.
		if ( 'complete' === $payment->status ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as payment is already complete.' );

			return;
		}

		if ( empty( $this->event->resource->status ) || 'COMPLETED' !== strtoupper( $this->event->resource->status ) ) {
			throw new \Exception( 'Capture status is not complete.', 200 );
		}

		if ( ! isset( $event->resource->amount->value ) ) {
			throw new \Exception( 'Missing amount value.', 200 );
		}

		if ( ! isset( $event->resource->amount->currency_code ) || strtoupper( $event->resource->amount->currency_code ) !== strtoupper( $payment->currency ) ) {
			throw new \Exception( sprintf( 'Missing or invalid currency code. Expected: %s; PayPal: %s', $payment->currency, esc_html( $event->resource->amount->currency_code ) ), 200 );
		}

		$paypal_amount  = (float) $event->resource->amount->value;
		$payment_amount = edd_get_payment_amount( $payment->ID );

		if ( $paypal_amount < $payment_amount ) {
			edd_record_gateway_error(
				__( 'Webhook Error', 'easy-digital-downloads' ),
				sprintf(
				/* Translators: %s is the webhook data */
					__( 'Invalid payment about in webhook response. Webhook data: %s', 'easy-digital-downloads' ),
					json_encode( $event )
				)
			);

			edd_update_payment_status( $payment->ID, 'failed' );
			edd_insert_payment_note( $payment->ID, sprintf(
				__( 'Payment failed due to invalid amount in PayPal webhook. Expected amount: %s; PayPal amount: %s.', 'easy-digital-downloads' ),
				$payment_amount,
				esc_html( $paypal_amount )
			) );

			throw new \Exception( sprintf( 'Webhook amount (%s) doesn\'t match payment amount (%s).', esc_html( $paypal_amount ), esc_html( $payment_amount ) ), 200 );
		}

		edd_update_payment_status( $payment->ID, 'complete' );
	}
}
