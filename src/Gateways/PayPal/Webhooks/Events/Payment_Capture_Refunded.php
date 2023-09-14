<?php
/**
 * Webhook Events:
 *
 * - PAYMENT.CAPTURE.REFUNDED - Merchant refunds a sale.
 * - PAYMENT.CAPTURE.REVERSED - PayPal reverses a sale.
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

class Payment_Capture_Refunded extends Webhook_Event {

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
		// Bail if this refund transaction already exists.
		if ( $this->refund_transaction_exists() ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as refund transaction already exists.' );

			return;
		}

		$order = $this->get_order_from_refund();

		if ( 'refunded' === $order->status ) {
			edd_debug_log( 'PayPal Commerce - Exiting webhook, as payment status is already refunded.' );

			return;
		}

		$order_amount    = edd_get_payment_amount( $order->id );
		$refunded_amount = isset( $this->event->resource->amount->value ) ? $this->event->resource->amount->value : $order_amount;
		$currency        = isset( $this->event->resource->amount->currency_code ) ? $this->event->resource->amount->currency_code : $order->currency;

		$payment_note = sprintf(
			/* Translators: %1$s - Amount refunded; %2$s - Original payment ID; %3$s - Refund transaction ID */
			esc_html__( 'Amount: %1$s; Payment transaction ID: %2$s; Refund transaction ID: %3$s', 'easy-digital-downloads' ),
			edd_currency_filter( edd_format_amount( $refunded_amount ), $currency ),
			esc_html( $order->get_transaction_id() ),
			esc_html( $this->event->resource->id )
		);

		// Partial refund.
		if ( (float) $refunded_amount < (float) $order_amount ) {
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => __( 'Partial refund processed in PayPal.', 'easy-digital-downloads' ) . ' ' . $payment_note,
				)
			);
			edd_update_order_status( $order->id, 'partially_refunded' );
		} else {
			// Full refund.
			edd_add_note(
				array(
					'object_type' => 'order',
					'object_id'   => $order->id,
					'content'     => __( 'Full refund processed in PayPal.', 'easy-digital-downloads' ) . ' ' . $payment_note,
				)
			);
			$refund_id = edd_refund_order( $order->id );
			if ( $refund_id ) {
				edd_add_order_transaction(
					array(
						'object_type'    => 'order',
						'object_id'      => $refund_id,
						'transaction_id' => $this->event->resource->id,
						'gateway'        => 'paypal_commerce',
						'total'          => $refunded_amount,
						'status'         => 'complete',
						'currency'       => $this->event->resource->amount->currency_code,
					)
				);
			} else {
				edd_update_order_status( $order->id, 'refunded' );
			}
		}
	}

	/**
	 * Determines whether a transaction record exists for this refund.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function refund_transaction_exists() {
		if ( ! isset( $this->event->resource->id ) ) {
			throw new \Exception( 'No resource ID found.', 200 );
		}

		$transaction = edd_get_order_transaction_by( 'transaction_id', $this->event->resource->id );

		return ! empty( $transaction );
	}
}
