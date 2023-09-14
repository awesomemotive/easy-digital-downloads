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

namespace EDD\Gateways\PayPal\Webhooks\Events;

use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;

class Payment_Capture_Completed extends Webhook_Event {

	/**
	 * Processes the event.
	 *
	 * @throws API_Exception
	 * @throws Authentication_Exception
	 * @throws \Exception
	 *
	 * @since 2.11
	 */
	protected function process_event() {
		$order = $this->get_order_from_capture();

		// Bail if the payment has already been completed.
		if ( 'complete' === $order->status ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as order is already complete.' );

			return;
		}

		if ( empty( $this->event->resource->status ) || 'COMPLETED' !== strtoupper( $this->event->resource->status ) ) {
			throw new \Exception( 'Capture status is not complete.', 200 );
		}

		if ( ! isset( $this->event->resource->amount->value ) ) {
			throw new \Exception( 'Missing amount value.', 200 );
		}

		if ( ! isset( $this->event->resource->amount->currency_code ) || strtoupper( $this->event->resource->amount->currency_code ) !== strtoupper( $order->currency ) ) {
			throw new \Exception( sprintf( 'Missing or invalid currency code. Expected: %s; PayPal: %s', $order->currency, esc_html( $this->event->resource->amount->currency_code ) ), 200 );
		}

		$paypal_amount = (float) $this->event->resource->amount->value;
		$order_amount  = edd_get_payment_amount( $order->id );

		if ( $paypal_amount < $order_amount ) {
			edd_record_gateway_error(
				__( 'Webhook Error', 'easy-digital-downloads' ),
				sprintf(
					/* Translators: %s is the webhook data */
					__( 'Invalid payment amount in webhook response. Webhook data: %s', 'easy-digital-downloads' ),
					json_encode( $this->event )
				)
			);

			edd_update_order_status( $order->id, 'failed' );
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => sprintf(
						/* translators: %1$s is the order amount, %2$s is the PayPal amount */
						__( 'Payment failed due to invalid amount in PayPal webhook. Expected amount: %1$s; PayPal amount: %2$s.', 'easy-digital-downloads' ),
						$order_amount,
						esc_html( $paypal_amount )
					),
				)
			);

			throw new \Exception( sprintf( 'Webhook amount (%s) doesn\'t match payment amount (%s).', esc_html( $paypal_amount ), esc_html( $order_amount ) ), 200 );
		}

		edd_update_order_status( $order->id, 'complete' );
	}
}
